<?php
/**
 * User: jensk
 * Date: 30-1-2017
 * Time: 13:32
 */

namespace library\components\cms\configuration;


use library\cc\Request;
use library\components\cms\CmsRouting;
use library\components\CmsComponent;

class ImageSetRouting implements CmsRouting
{

	/**
	 * CmsRouting constructor.
	 *
	 * @param Request      $request
	 * @param string       $relativeCmsUri
	 * @param CmsComponent $cmsComponent
	 */
	public function __construct($request, $relativeCmsUri, $cmsComponent)
	{
		if ($relativeCmsUri == '/configuration/image-set') {
			$this->overviewRoute($cmsComponent);
		} elseif ($relativeCmsUri == '/configuration/image-set/edit' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
			$this->editRoute($request, $cmsComponent);
		} elseif ($relativeCmsUri == '/configuration/image-set/new') {
			$this->newRoute($request, $cmsComponent);
		} elseif ($relativeCmsUri == '/configuration/image-set/delete' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
			$this->deleteRoute($request, $cmsComponent);
		}
	}

	/**
	 * @param $cmsComponent
	 */
	private function overviewRoute($cmsComponent)
	{
		$cmsComponent->subTemplate = 'cms/configuration/image-set';
		$cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_CONFIGURATION);
		$cmsComponent->setParameter(CmsComponent::PARAMETER_IMAGE_SET, $cmsComponent->storage->getImageSet());
	}

	/**
	 * @param $request
	 * @param $cmsComponent
	 */
	private function editRoute($request, $cmsComponent)
	{
		$cmsComponent->subTemplate = 'cms/configuration/image-set-form';
		$cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_CONFIGURATION);
		$imageSet = $cmsComponent->storage->getImageSetBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]);
		if (isset($request::$post[CmsComponent::POST_PARAMETER_TITLE])) {
			$cmsComponent->storage->saveImageSet($request::$get[CmsComponent::GET_PARAMETER_SLUG], $request::$post);
			header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/configuration/image-set');
			exit;
		}
		$cmsComponent->setParameter(CmsComponent::PARAMETER_IMAGE_SET, $imageSet);
	}

	/**
	 * @param $request
	 * @param $cmsComponent
	 */
	private function newRoute($request, $cmsComponent)
	{
		$cmsComponent->subTemplate = 'cms/configuration/image-set-form';
		$cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_CONFIGURATION);
		if (isset($request::$post[CmsComponent::POST_PARAMETER_TITLE])) {
			$cmsComponent->storage->addImageSet($request::$post);
			header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/configuration/image-set');
			exit;
		}
	}

	/**
	 * @param $request
	 * @param $cmsComponent
	 */
	private function deleteRoute($request, $cmsComponent)
	{
		$cmsComponent->storage->deleteImageSetBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]);
		header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/configuration/image-set');
		exit;
	}
}