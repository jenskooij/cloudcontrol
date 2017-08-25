<?php
/**
 * User: jensk
 * Date: 30-1-2017
 * Time: 12:46
 */

namespace CloudControl\Cms\components\cms;

use CloudControl\Cms\cc\Request;
use CloudControl\Cms\components\CmsComponent;

class FilesRouting implements CmsRouting
{
	/**
	 * FilesRouting constructor.
	 *
	 * @param Request $request
	 * @param $relativeCmsUri
	 * @param CmsComponent $cmsComponent
	 */
	public function __construct($request, $relativeCmsUri, $cmsComponent)
	{
		if ($relativeCmsUri == '/files') {
			$this->overviewRoute($cmsComponent);
		} elseif ($relativeCmsUri == '/files/new') {
			$this->newRoute($request, $cmsComponent);
		} elseif ($relativeCmsUri == '/files/get' && isset($request::$get[CmsComponent::FILES_PARAMETER_FILE])) {
			$this->downloadRoute($request::$get[CmsComponent::FILES_PARAMETER_FILE], $cmsComponent);
		} elseif ($relativeCmsUri == '/files/delete' && isset($request::$get[CmsComponent::FILES_PARAMETER_FILE])) {
			$this->deleteRoute($request, $cmsComponent);
		}
	}

	/**
	 * @param $slug
	 * @param CmsComponent $cmsComponent
	 */
	private function downloadRoute($slug, $cmsComponent)
	{
		$file = $cmsComponent->storage->getFiles()->getFileByName($slug);
		$path = realpath($cmsComponent->storage->getFiles()->getFilesDir());
		$quoted = sprintf('"%s"', addcslashes(basename($path . '/' . $file->file), '"\\'));
		$size = filesize($path . '/' . $file->file);

		header('Content-Description: File Transfer');
		header('Content-Type: ' . $file->type);
		header('Content-Disposition: attachment; filename=' . $quoted);
		header('Content-Transfer-Encoding: binary');
		header('Connection: Keep-Alive');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . $size);

		readfile($path . '/' . $file->file);
		exit;
	}

	/**
	 * @param CmsComponent $cmsComponent
	 */
	private function overviewRoute($cmsComponent)
	{
		$cmsComponent->subTemplate = 'files';
		$cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_FILES);
		$cmsComponent->setParameter(CmsComponent::PARAMETER_FILES, $cmsComponent->storage->getFiles()->getFiles());
	}

	/**
	 * @param $request
	 * @param CmsComponent $cmsComponent
	 */
	private function newRoute($request, $cmsComponent)
	{
		$cmsComponent->subTemplate = 'files/form';
		$cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_FILES);
		if (isset($_FILES[CmsComponent::FILES_PARAMETER_FILE])) {
			$cmsComponent->storage->getFiles()->addFile($_FILES[CmsComponent::FILES_PARAMETER_FILE]);
			header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/files');
			exit;
		}
	}

	/**
	 * @param $request
	 * @param CmsComponent $cmsComponent
	 */
	private function deleteRoute($request, $cmsComponent)
	{
		$cmsComponent->storage->getFiles()->deleteFileByName($request::$get[CmsComponent::FILES_PARAMETER_FILE]);
		header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/files');
		exit;
	}

}