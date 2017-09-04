<?php
/**
 * Created by IntelliJ IDEA.
 * User: Jens
 * Date: 29-1-2017
 * Time: 16:30
 */

namespace CloudControl\Cms\components\cms;


use CloudControl\Cms\cc\Request;
use CloudControl\Cms\components\CmsComponent;

class SitemapRouting implements CmsRouting
{

    /**
     * SitemapRouting constructor.
     * @param \CloudControl\Cms\cc\Request $request
     * @param mixed|string $relativeCmsUri
     * @param CmsComponent $cmsComponent
     */
    public function __construct(Request $request, $relativeCmsUri, CmsComponent $cmsComponent)
    {
        if ($relativeCmsUri == '/sitemap') {
            $this->overviewRoute($request, $cmsComponent);
        } elseif ($relativeCmsUri == '/sitemap/new') {
            $this->newRoute($request, $cmsComponent);
        } elseif ($relativeCmsUri == '/sitemap/edit' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
            $this->editRoute($request, $cmsComponent);
        } elseif ($relativeCmsUri == '/sitemap/delete' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
            $this->deleteRoute($request, $cmsComponent);
        } else {
            $this->redirectRoutes($relativeCmsUri, $request, $cmsComponent);
        }
    }

    /**
     * @param $request
     * @param CmsComponent $cmsComponent
     */
    private function overviewRoute($request, $cmsComponent)
    {
        $cmsComponent->subTemplate = 'sitemap';
        if (isset($request::$post[CmsComponent::POST_PARAMETER_SAVE])) {
            $cmsComponent->storage->getSitemap()->saveSitemap($request::$post);
        }
        $cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_SITEMAP);
        $cmsComponent->setParameter(CmsComponent::PARAMETER_SITEMAP, $cmsComponent->storage->getSitemap()->getSitemap());
    }

    /**
     * @param $request
     * @param CmsComponent $cmsComponent
     */
    private function newRoute($request, $cmsComponent)
    {
        $cmsComponent->subTemplate = 'sitemap/form';
        $cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_SITEMAP);
        if (isset($request::$post[CmsComponent::POST_PARAMETER_TITLE], $request::$post[CmsComponent::POST_PARAMETER_TEMPLATE], $request::$post[CmsComponent::POST_PARAMETER_COMPONENT])) {
            $cmsComponent->storage->getSitemap()->addSitemapItem($request::$post);
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/sitemap');
            exit;
        }
    }

    /**
     * @param $request
     * @param CmsComponent $cmsComponent
     */
    private function editRoute($request, $cmsComponent)
    {
        $cmsComponent->subTemplate = 'sitemap/form';
        $cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_SITEMAP);
        $sitemapItem = $cmsComponent->storage->getSitemap()->getSitemapItemBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]);
        if (isset($request::$post[CmsComponent::POST_PARAMETER_TITLE], $request::$post[CmsComponent::POST_PARAMETER_TEMPLATE], $request::$post[CmsComponent::POST_PARAMETER_COMPONENT])) {
            $cmsComponent->storage->getSitemap()->saveSitemapItem($request::$get[CmsComponent::GET_PARAMETER_SLUG], $request::$post);
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/sitemap');
            exit;
        }
        $cmsComponent->setParameter(CmsComponent::PARAMETER_SITEMAP_ITEM, $sitemapItem);
    }

    /**
     * @param $request
     * @param CmsComponent $cmsComponent
     */
    private function deleteRoute($request, $cmsComponent)
    {
        $cmsComponent->storage->getSitemap()->deleteSitemapItemBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]);
        header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/sitemap');
        exit;
    }

    private function redirectRoutes($relativeCmsUri, $request, $cmsComponent)
    {
        if ($relativeCmsUri == '/sitemap/redirects') {
            $this->redirectsOverviewRoute($cmsComponent);
        } elseif ($relativeCmsUri == '/sitemap/redirects/new') {
            $this->redirectsNewRoute($request, $cmsComponent);
        } elseif ($relativeCmsUri == '/sitemap/redirects/edit' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
            $this->redirectEditRoute($request, $cmsComponent);
        } elseif ($relativeCmsUri == '/sitemap/redirects/delete' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
            $this->redirectDeleteRoute($request, $cmsComponent);
        }
    }

    private function redirectsOverviewRoute($cmsComponent)
    {
        $cmsComponent->subTemplate = 'sitemap/redirects';
        $cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_SITEMAP);
        $cmsComponent->setParameter(CmsComponent::PARAMETER_REDIRECTS, $cmsComponent->storage->getRedirects()->getRedirects());
    }

    private function redirectsNewRoute($request, $cmsComponent)
    {
        $cmsComponent->subTemplate = 'sitemap/redirects-form';
        $cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_SITEMAP);
        if (isset($request::$post[CmsComponent::POST_PARAMETER_TITLE], $request::$post[CmsComponent::POST_PARAMETER_FROM_URL], $request::$post[CmsComponent::POST_PARAMETER_TO_URL])) {
            $cmsComponent->storage->getRedirects()->addRedirect($request::$post);
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/sitemap/redirects');
            exit;
        }
    }

    private function redirectEditRoute($request, $cmsComponent)
    {
        $cmsComponent->subTemplate = 'sitemap/redirects-form';
        $cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_SITEMAP);
        $redirect = $cmsComponent->storage->getRedirects()->getRedirectBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]);
        if (isset($request::$post[CmsComponent::POST_PARAMETER_TITLE], $request::$post[CmsComponent::POST_PARAMETER_FROM_URL], $request::$post[CmsComponent::POST_PARAMETER_TO_URL])) {
            $cmsComponent->storage->getRedirects()->saveRedirect($request::$get[CmsComponent::GET_PARAMETER_SLUG], $request::$post);
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/sitemap/redirects');
            exit;
        }
        $cmsComponent->setParameter(CmsComponent::PARAMETER_REDIRECT, $redirect);
    }

    private function redirectDeleteRoute($request, $cmsComponent)
    {
        $cmsComponent->storage->getRedirects()->deleteRedirectBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]);
        header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/sitemap/redirects');
        exit;
    }
}