<?php
/**
 * Created by jensk on 4-9-2017.
 */

namespace CloudControl\Cms\components\cms;


use CloudControl\Cms\cc\Request;
use CloudControl\Cms\components\CmsComponent;

class RedirectRouting implements CmsRouting
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
        if ($relativeCmsUri == '/redirects') {
            $this->redirectsOverviewRoute($cmsComponent);
        } elseif ($relativeCmsUri == '/redirects/new') {
            $this->redirectsNewRoute($request, $cmsComponent);
        } elseif ($relativeCmsUri == '/redirects/edit' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
            $this->redirectEditRoute($request, $cmsComponent);
        } elseif ($relativeCmsUri == '/redirects/delete' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
            $this->redirectDeleteRoute($request, $cmsComponent);
        }
    }

    private function redirectsOverviewRoute(CmsComponent $cmsComponent)
    {
        $cmsComponent->subTemplate = 'redirects';
        $cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_REDIRECTS);
        $cmsComponent->setParameter(CmsComponent::PARAMETER_REDIRECTS, $cmsComponent->storage->getRedirects()->getRedirects());
    }

    private function redirectsNewRoute(Request $request, CmsComponent $cmsComponent)
    {
        $cmsComponent->subTemplate = 'redirects/form';
        $cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_REDIRECTS);
        if (isset($request::$post[CmsComponent::POST_PARAMETER_TITLE], $request::$post[CmsComponent::POST_PARAMETER_FROM_URL], $request::$post[CmsComponent::POST_PARAMETER_TO_URL])) {
            $cmsComponent->storage->getRedirects()->addRedirect($request::$post);
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/redirects');
            exit;
        }
    }

    private function redirectEditRoute(Request $request, CmsComponent $cmsComponent)
    {
        $cmsComponent->subTemplate = 'redirects/form';
        $cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_REDIRECTS);
        $redirect = $cmsComponent->storage->getRedirects()->getRedirectBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]);
        if (isset($request::$post[CmsComponent::POST_PARAMETER_TITLE], $request::$post[CmsComponent::POST_PARAMETER_FROM_URL], $request::$post[CmsComponent::POST_PARAMETER_TO_URL])) {
            $cmsComponent->storage->getRedirects()->saveRedirect($request::$get[CmsComponent::GET_PARAMETER_SLUG], $request::$post);
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/redirects');
            exit;
        }
        $cmsComponent->setParameter(CmsComponent::PARAMETER_REDIRECT, $redirect);
    }

    private function redirectDeleteRoute(Request $request, CmsComponent $cmsComponent)
    {
        $cmsComponent->storage->getRedirects()->deleteRedirectBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]);
        header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/redirects');
        exit;
    }
}