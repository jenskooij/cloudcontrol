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
			$cmsComponent->subTemplate = 'cms/configuration/users';
			$cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_CONFIGURATION);
			$cmsComponent->setParameter(CmsComponent::PARAMETER_USERS, $cmsComponent->storage->getUsers());
		} elseif ($relativeCmsUri == '/configuration/users/new') {
			$cmsComponent->subTemplate = 'cms/configuration/users-form';
			$cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_CONFIGURATION);
			if (isset($request::$post[CmsComponent::POST_PARAMETER_USERNAME])) {
				$cmsComponent->storage->addUser($request::$post);
				header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/configuration/users');
				exit;
			}
		} elseif ($relativeCmsUri == '/configuration/users/delete' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
			$cmsComponent->storage->deleteUserBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]);
			header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/configuration/users');
			exit;
		} elseif ($relativeCmsUri == '/configuration/users/edit' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
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
}