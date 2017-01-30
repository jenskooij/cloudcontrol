<?php
/**
 * User: jensk
 * Date: 30-1-2017
 * Time: 13:27
 */

namespace library\components\cms\configuration;


use library\cc\Request;
use library\components\cms\CmsRouting;
use library\components\CmsComponent;

class BricksRouting implements CmsRouting
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
		if ($relativeCmsUri == '/configuration/bricks') {
			$this->overviewRoute($cmsComponent);
		} elseif ($relativeCmsUri == '/configuration/bricks/new') {
			$this->newRoute($request, $cmsComponent);
		} elseif ($relativeCmsUri == '/configuration/bricks/edit' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
			$this->editRoute($request, $cmsComponent);
		} elseif ($relativeCmsUri == '/configuration/bricks/delete' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
			$this->deleteRoute($request, $cmsComponent);
		}
	}

	/**
	 * @param $cmsComponent
	 */
	private function overviewRoute($cmsComponent)
	{
		$cmsComponent->subTemplate = 'cms/configuration/bricks';
		$cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_CONFIGURATION);
		$cmsComponent->setParameter(CmsComponent::PARAMETER_BRICKS, $cmsComponent->storage->getBricks());
	}

	/**
	 * @param $request
	 * @param $cmsComponent
	 */
	private function newRoute($request, $cmsComponent)
	{
		$cmsComponent->subTemplate = 'cms/configuration/bricks-form';
		$cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_CONFIGURATION);
		if (isset($request::$post[CmsComponent::POST_PARAMETER_TITLE])) {
			$cmsComponent->storage->addBrick($request::$post);
			header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/configuration/bricks');
			exit;
		}
	}

	/**
	 * @param $request
	 * @param $cmsComponent
	 */
	private function editRoute($request, $cmsComponent)
	{
		$cmsComponent->subTemplate = 'cms/configuration/bricks-form';
		$cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_CONFIGURATION);
		$brick = $cmsComponent->storage->getBrickBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]);
		if (isset($request::$post[CmsComponent::POST_PARAMETER_TITLE])) {
			$cmsComponent->storage->saveBrick($request::$get[CmsComponent::GET_PARAMETER_SLUG], $request::$post);
			header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/configuration/bricks');
			exit;
		}
		$cmsComponent->setParameter(CmsComponent::PARAMETER_BRICK, $brick);
	}

	/**
	 * @param $request
	 * @param $cmsComponent
	 */
	private function deleteRoute($request, $cmsComponent)
	{
		$cmsComponent->storage->deleteBrickBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]);
		header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/configuration/bricks');
		exit;
	}
}