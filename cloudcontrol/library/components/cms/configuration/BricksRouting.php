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
			$cmsComponent->subTemplate = 'cms/configuration/bricks';
			$cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_CONFIGURATION);
			$cmsComponent->setParameter(CmsComponent::PARAMETER_BRICKS, $cmsComponent->storage->getBricks());
		} elseif ($relativeCmsUri == '/configuration/bricks/new') {
			$cmsComponent->subTemplate = 'cms/configuration/bricks-form';
			$cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_CONFIGURATION);
			if (isset($request::$post[CmsComponent::POST_PARAMETER_TITLE])) {
				$cmsComponent->storage->addBrick($request::$post);
				header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/configuration/bricks');
				exit;
			}
		} elseif ($relativeCmsUri == '/configuration/bricks/edit' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
			$cmsComponent->subTemplate = 'cms/configuration/bricks-form';
			$cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_CONFIGURATION);
			$brick = $cmsComponent->storage->getBrickBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]);
			if (isset($request::$post[CmsComponent::POST_PARAMETER_TITLE])) {
				$cmsComponent->storage->saveBrick($request::$get[CmsComponent::GET_PARAMETER_SLUG], $request::$post);
				header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/configuration/bricks');
				exit;
			}
			$cmsComponent->setParameter(CmsComponent::PARAMETER_BRICK, $brick);
		} elseif ($relativeCmsUri == '/configuration/bricks/delete' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
			$cmsComponent->storage->deleteBrickBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]);
			header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/configuration/bricks');
			exit;
		} elseif ($relativeCmsUri == '/configuration/image-set') {
			$cmsComponent->subTemplate = 'cms/configuration/image-set';
			$cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_CONFIGURATION);
			$cmsComponent->setParameter(CmsComponent::PARAMETER_IMAGE_SET, $cmsComponent->storage->getImageSet());
		}
	}
}