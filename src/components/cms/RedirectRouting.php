<?php
/**
 * Created by jensk on 4-9-2017.
 */

namespace CloudControl\Cms\components\cms;


use CloudControl\Cms\cc\Request;
use CloudControl\Cms\components\CmsComponent;

class RedirectRouting extends CmsRouting
{

    protected static $routes = array(
        '/redirects' => 'overviewRoute',
        '/redirects/new' => 'newRoute',
        '/redirects/edit' => 'editRoute',
        '/redirects/delete' => 'deleteRoute',
    );

    /**
     * CmsRouting constructor.
     *
     * @param Request $request
     * @param string $relativeCmsUri
     * @param CmsComponent $cmsComponent
     * @throws \Exception
     */
    public function __construct(Request $request, $relativeCmsUri, CmsComponent $cmsComponent)
    {
        $this->doRouting($request, $relativeCmsUri, $cmsComponent);
    }

    protected function overviewRoute(Request $request, CmsComponent $cmsComponent)
    {
        $cmsComponent->subTemplate = 'redirects';
        $cmsComponent->setParameter(CmsConstants::PARAMETER_MAIN_NAV_CLASS, CmsConstants::PARAMETER_REDIRECTS);
        $cmsComponent->setParameter(CmsConstants::PARAMETER_REDIRECTS,
            $cmsComponent->storage->getRedirects()->getRedirects());
    }

    protected function newRoute(Request $request, CmsComponent $cmsComponent)
    {
        $cmsComponent->subTemplate = 'redirects/form';
        $cmsComponent->setParameter(CmsConstants::PARAMETER_MAIN_NAV_CLASS, CmsConstants::PARAMETER_REDIRECTS);
        if (isset($request::$post[CmsConstants::POST_PARAMETER_TITLE], $request::$post[CmsConstants::POST_PARAMETER_FROM_URL], $request::$post[CmsConstants::POST_PARAMETER_TO_URL])) {
            $cmsComponent->storage->getRedirects()->addRedirect($request::$post);
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/redirects');
            exit;
        }
    }

    protected function editRoute(Request $request, CmsComponent $cmsComponent)
    {
        $cmsComponent->subTemplate = 'redirects/form';
        $cmsComponent->setParameter(CmsConstants::PARAMETER_MAIN_NAV_CLASS, CmsConstants::PARAMETER_REDIRECTS);
        $redirect = $cmsComponent->storage->getRedirects()->getRedirectBySlug($request::$get[CmsConstants::GET_PARAMETER_SLUG]);
        if (isset($request::$post[CmsConstants::POST_PARAMETER_TITLE], $request::$post[CmsConstants::POST_PARAMETER_FROM_URL], $request::$post[CmsConstants::POST_PARAMETER_TO_URL])) {
            $cmsComponent->storage->getRedirects()->saveRedirect($request::$get[CmsConstants::GET_PARAMETER_SLUG],
                $request::$post);
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/redirects');
            exit;
        }
        $cmsComponent->setParameter(CmsConstants::PARAMETER_REDIRECT, $redirect);
    }

    protected function deleteRoute(Request $request, CmsComponent $cmsComponent)
    {
        $cmsComponent->storage->getRedirects()->deleteRedirectBySlug($request::$get[CmsConstants::GET_PARAMETER_SLUG]);
        header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/redirects');
        exit;
    }
}