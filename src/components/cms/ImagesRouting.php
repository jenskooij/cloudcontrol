<?php
/**
 * User: Jens
 * Date: 29-1-2017
 * Time: 16:11
 */

namespace CloudControl\Cms\components\cms;


use CloudControl\Cms\components\CmsComponent;

class ImagesRouting implements CmsRouting
{

    /**
     * ImagesRouting constructor.
     * @param \CloudControl\Cms\cc\Request $request
     * @param mixed|string $relativeCmsUri
     * @param CmsComponent $cmsComponent
     */
    public function __construct($request, $relativeCmsUri, $cmsComponent)
    {
        if ($relativeCmsUri == '/images') {
			$this->overviewRoute($cmsComponent);
        } elseif ($relativeCmsUri == '/images.json') {
			$this->jsonRoute($cmsComponent);
        } elseif ($relativeCmsUri == '/images/new') {
			$this->newRoute($request, $cmsComponent);
        } elseif ($relativeCmsUri == '/images/delete' && isset($request::$get[CmsComponent::FILES_PARAMETER_FILE])) {
			$this->deleteRoute($request, $cmsComponent);
        } elseif ($relativeCmsUri == '/images/show' && isset($request::$get[CmsComponent::FILES_PARAMETER_FILE])) {
			$this->showRoute($request, $cmsComponent);
        }
    }

	/**
	 * @param CmsComponent $cmsComponent
	 */
	private function overviewRoute($cmsComponent)
	{
		$cmsComponent->subTemplate = 'images';
		$cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_IMAGES);
		$cmsComponent->setParameter(CmsComponent::PARAMETER_IMAGES, $cmsComponent->storage->getImages()->getImages());
		$cmsComponent->setParameter(CmsComponent::PARAMETER_SMALLEST_IMAGE, $cmsComponent->storage->getImageSet()->getSmallestImageSet()->slug);
	}

	/**
	 * @param CmsComponent $cmsComponent
	 */
	private function jsonRoute($cmsComponent)
	{
		header(CmsComponent::CONTENT_TYPE_APPLICATION_JSON);
		die(json_encode($cmsComponent->storage->getImages()));
	}

	/**
	 * @param $request
	 * @param CmsComponent $cmsComponent
	 */
	private function newRoute($request, $cmsComponent)
	{
		$cmsComponent->subTemplate = 'images/form';
		$cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_IMAGES);
		if (isset($_FILES[CmsComponent::FILES_PARAMETER_FILE])) {
			$cmsComponent->storage->getImages()->addImage($_FILES[CmsComponent::FILES_PARAMETER_FILE]);
			header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/images');
			exit;
		}
	}

	/**
	 * @param $request
	 * @param CmsComponent $cmsComponent
	 */
	private function deleteRoute($request, $cmsComponent)
	{
		$cmsComponent->storage->getImages()->deleteImageByName($request::$get[CmsComponent::FILES_PARAMETER_FILE]);
		header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/images');
		exit;
	}

	/**
	 * @param $request
	 * @param CmsComponent $cmsComponent
	 */
	private function showRoute($request, $cmsComponent)
	{
		$cmsComponent->subTemplate = 'images/show';
		$cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_IMAGES);
		$cmsComponent->setParameter(CmsComponent::PARAMETER_IMAGE, $cmsComponent->storage->getImages()->getImageByName($request::$get[CmsComponent::FILES_PARAMETER_FILE]));
	}
}