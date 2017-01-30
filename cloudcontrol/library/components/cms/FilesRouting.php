<?php
/**
 * User: jensk
 * Date: 30-1-2017
 * Time: 12:46
 */

namespace library\components\cms;

use library\cc\Request;
use library\components\CmsComponent;

class FilesRouting
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
			$cmsComponent->subTemplate = 'cms/files';
			$cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_FILES);
			$cmsComponent->setParameter(CmsComponent::PARAMETER_FILES, $cmsComponent->storage->getFiles());
		} elseif ($relativeCmsUri == '/files/new') {
			$cmsComponent->subTemplate = 'cms/files/form';
			$cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_FILES);
			if (isset($_FILES[CmsComponent::FILES_PARAMETER_FILE])) {
				$cmsComponent->storage->addFile($_FILES[CmsComponent::FILES_PARAMETER_FILE]);
				header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/files');
				exit;
			}
		} elseif ($relativeCmsUri == '/files/get' && isset($request::$get[CmsComponent::FILES_PARAMETER_FILE])) {
			$this->downloadFile($request::$get[CmsComponent::FILES_PARAMETER_FILE], $cmsComponent);
		} elseif ($relativeCmsUri == '/files/delete' && isset($request::$get[CmsComponent::FILES_PARAMETER_FILE])) {
			$cmsComponent->storage->deleteFileByName($request::$get[CmsComponent::FILES_PARAMETER_FILE]);
			header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/files');
			exit;
		}
	}

	/**
	 * @param $slug
	 * @param $cmsComponent
	 */
	private function downloadFile($slug, $cmsComponent)
	{
		$file = $cmsComponent->storage->getFileByName($slug);
		$path = realpath(__DIR__ . '/../../../www/files/');
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

}