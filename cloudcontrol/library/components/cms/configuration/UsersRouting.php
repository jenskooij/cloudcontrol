<?php
/**
 * Created by IntelliJ IDEA.
 * User: jensk
 * Date: 30-1-2017
 * Time: 13:08
 */

namespace library\components\cms\configuration;


use library\cc\Request;
use library\components\cms\CmsRouting;
use library\components\CmsComponent;

class UsersRouting implements CmsRouting
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
		if ($relativeCmsUri == '/configuration/users') {
			$this->overviewRoute($cmsComponent);
		} elseif ($relativeCmsUri == '/configuration/users/new') {
			$this->newRoute($request, $cmsComponent);
		} elseif ($relativeCmsUri == '/configuration/users/delete' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
			$this->deleteRoute($request, $cmsComponent);
		} elseif ($relativeCmsUri == '/configuration/users/edit' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
			$this->editRoute($request, $cmsComponent);
		}
	}

	/**
	 * @param $cmsComponent
	 */
	private function overviewRoute($cmsComponent)
	{
		$cmsComponent->subTemplate = 'cms/configuration/users';
		$cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_CONFIGURATION);
		$cmsComponent->setParameter(CmsComponent::PARAMETER_USERS, $cmsComponent->storage->getUsers());
	}

	/**
	 * @param $request
	 * @param $cmsComponent
	 */
	private function newRoute($request, $cmsComponent)
	{
		$cmsComponent->subTemplate = 'cms/configuration/users-form';
		$cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_CONFIGURATION);
		if (isset($request::$post[CmsComponent::POST_PARAMETER_USERNAME])) {
			$cmsComponent->storage->addUser($request::$post);
			header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/configuration/users');
			exit;
		}
	}

	/**
	 * @param $request
	 * @param $cmsComponent
	 */
	private function deleteRoute($request, $cmsComponent)
	{
		$cmsComponent->storage->deleteUserBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]);
		header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/configuration/users');
		exit;
	}

	/**
	 * @param $request
	 * @param $cmsComponent
	 */
	private function editRoute($request, $cmsComponent)
	{
		$cmsComponent->subTemplate = 'cms/configuration/users-form';
		$cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_CONFIGURATION);
		$cmsComponent->setParameter(CmsComponent::PARAMETER_USER, $cmsComponent->storage->getUserBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]));
		if (isset($_POST[CmsComponent::POST_PARAMETER_USERNAME])) {
			$cmsComponent->storage->saveUser($request::$get[CmsComponent::GET_PARAMETER_SLUG], $request::$post);
			header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/configuration/users');
			exit;
		}
	}
}