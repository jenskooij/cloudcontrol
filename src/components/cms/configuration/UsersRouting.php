<?php
/**
 * Created by IntelliJ IDEA.
 * User: jensk
 * Date: 30-1-2017
 * Time: 13:08
 */

namespace CloudControl\Cms\components\cms\configuration;


use CloudControl\Cms\cc\Request;
use CloudControl\Cms\components\cms\CmsConstants;
use CloudControl\Cms\components\cms\CmsRouting;
use CloudControl\Cms\components\CmsComponent;

class UsersRouting implements CmsRouting
{

    /**
     * CmsRouting constructor.
     *
     * @param Request $request
     * @param string $relativeCmsUri
     * @param CmsComponent $cmsComponent
     */
    public function __construct(Request $request, $relativeCmsUri, CmsComponent $cmsComponent)
    {
        if ($relativeCmsUri == '/configuration/users') {
            $this->overviewRoute($cmsComponent);
        } elseif ($relativeCmsUri == '/configuration/users/new') {
            $this->newRoute($request, $cmsComponent);
        } elseif ($relativeCmsUri == '/configuration/users/delete' && isset($request::$get[CmsConstants::GET_PARAMETER_SLUG])) {
            $this->deleteRoute($request, $cmsComponent);
        } elseif ($relativeCmsUri == '/configuration/users/edit' && isset($request::$get[CmsConstants::GET_PARAMETER_SLUG])) {
            $this->editRoute($request, $cmsComponent);
        }
    }

    /**
     * @param CmsComponent $cmsComponent
     */
    private function overviewRoute($cmsComponent)
    {
        $cmsComponent->subTemplate = 'configuration/users';
        $cmsComponent->setParameter(CmsConstants::PARAMETER_MAIN_NAV_CLASS, CmsConstants::PARAMETER_CONFIGURATION);
        $cmsComponent->setParameter(CmsConstants::PARAMETER_USERS, $cmsComponent->storage->getUsers()->getUsers());
    }

    /**
     * @param Request $request
     * @param CmsComponent $cmsComponent
     */
    private function newRoute($request, $cmsComponent)
    {
        $cmsComponent->subTemplate = 'configuration/users-form';
        $cmsComponent->setParameter(CmsConstants::PARAMETER_MAIN_NAV_CLASS, CmsConstants::PARAMETER_CONFIGURATION);
        if (isset($request::$post[CmsConstants::POST_PARAMETER_USERNAME])) {
            $cmsComponent->storage->getUsers()->addUser($request::$post);
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/configuration/users');
            exit;
        }
    }

    /**
     * @param Request $request
     * @param CmsComponent $cmsComponent
     */
    private function deleteRoute($request, $cmsComponent)
    {
        $cmsComponent->storage->getUsers()->deleteUserBySlug($request::$get[CmsConstants::GET_PARAMETER_SLUG]);
        header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/configuration/users');
        exit;
    }

    /**
     * @param Request $request
     * @param CmsComponent $cmsComponent
     */
    private function editRoute($request, $cmsComponent)
    {
        $cmsComponent->subTemplate = 'configuration/users-form';
        $cmsComponent->setParameter(CmsConstants::PARAMETER_MAIN_NAV_CLASS, CmsConstants::PARAMETER_CONFIGURATION);
        $cmsComponent->setParameter(CmsConstants::PARAMETER_USER, $cmsComponent->storage->getUsers()->getUserBySlug($request::$get[CmsConstants::GET_PARAMETER_SLUG]));
        if (isset($_POST[CmsConstants::POST_PARAMETER_USERNAME])) {
            $cmsComponent->storage->getUsers()->saveUser($request::$get[CmsConstants::GET_PARAMETER_SLUG], $request::$post);
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/configuration/users');
            exit;
        }
    }
}