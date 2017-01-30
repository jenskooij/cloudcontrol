<?php
/**
 * User: jensk
 * Date: 30-1-2017
 * Time: 13:37
 */

namespace library\components\cms\configuration;


use library\cc\Request;
use library\components\cms\CmsRouting;
use library\components\CmsComponent;

class ApplicationComponentRouting implements CmsRouting
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
		if ($relativeCmsUri == '/configuration/application-components') {
			$this->overviewRoute($cmsComponent);
		} elseif ($relativeCmsUri == '/configuration/application-components/new') {
			$this->newRoute($request, $cmsComponent);
		} elseif ($relativeCmsUri == '/configuration/application-components/edit' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
			$this->editRoute($request, $cmsComponent);
		} elseif ($relativeCmsUri == '/configuration/application-components/delete' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
			$this->deleteRoute($request, $cmsComponent);
		}
	}

	/**
	 * @param $cmsComponent
	 */
	private function overviewRoute($cmsComponent)
	{
		$cmsComponent->subTemplate = 'cms/configuration/application-components';
		$cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_CONFIGURATION);
		$cmsComponent->setParameter(CmsComponent::PARAMETER_APPLICATION_COMPONENTS, $cmsComponent->storage->getApplicationComponents());
	}

	/**
	 * @param $request
	 * @param $cmsComponent
	 */
	private function newRoute($request, $cmsComponent)
	{
		$cmsComponent->subTemplate = 'cms/configuration/application-components-form';
		$cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_CONFIGURATION);
		if (isset($request::$post[CmsComponent::POST_PARAMETER_TITLE])) {
			$cmsComponent->storage->addApplicationComponent($request::$post);
			header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/configuration/application-components');
			exit;
		}
	}

	/**
	 * @param $request
	 * @param $cmsComponent
	 */
	private function editRoute($request, $cmsComponent)
	{
		$cmsComponent->subTemplate = 'cms/configuration/application-components-form';
		$cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_CONFIGURATION);
		$applicationComponent = $cmsComponent->storage->getApplicationComponentBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]);
		if (isset($request::$post[CmsComponent::POST_PARAMETER_TITLE])) {
			$cmsComponent->storage->saveApplicationComponent($request::$get[CmsComponent::GET_PARAMETER_SLUG], $request::$post);
			header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/configuration/application-components');
			exit;
		}

		$cmsComponent->setParameter(CmsComponent::PARAMETER_APPLICATION_COMPONENT, $applicationComponent);
	}

	/**
	 * @param $request
	 * @param $cmsComponent
	 */
	private function deleteRoute($request, $cmsComponent)
	{
		$cmsComponent->storage->deleteApplicationComponentBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]);
		header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/configuration/application-components');
		exit;
	}
}