<?php
/**
 * Created by IntelliJ IDEA.
 * User: Jens
 * Date: 29-1-2017
 * Time: 16:30
 */

namespace library\components\cms;


use library\components\CmsComponent;

class SitemapRouting
{

    /**
     * SitemapRouting constructor.
     * @param \library\cc\Request $request
     * @param mixed|string $relativeCmsUri
     * @param CmsComponent $cmsComponent
     */
    public function __construct($request, $relativeCmsUri, $cmsComponent)
    {
        if ($relativeCmsUri == '/sitemap') {
            $cmsComponent->subTemplate = 'cms/sitemap';
            if (isset($request::$post[CmsComponent::POST_PARAMETER_SAVE])) {
                $cmsComponent->storage->saveSitemap($request::$post);
            }
            $cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_SITEMAP);
            $cmsComponent->setParameter(CmsComponent::PARAMETER_SITEMAP, $cmsComponent->storage->getSitemap());
        } elseif ($relativeCmsUri == '/sitemap/new') {
            $cmsComponent->subTemplate = 'cms/sitemap/form';
            $cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_SITEMAP);
            if (isset($request::$post[CmsComponent::POST_PARAMETER_TITLE], $request::$post[CmsComponent::POST_PARAMETER_TEMPLATE], $request::$post[CmsComponent::POST_PARAMETER_COMPONENT])) {
                $cmsComponent->storage->addSitemapItem($request::$post);
                header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/sitemap');
                exit;
            }
        } elseif ($relativeCmsUri == '/sitemap/edit' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
            $cmsComponent->subTemplate = 'cms/sitemap/form';
            $cmsComponent->setParameter(CmsComponent::PARAMETER_MAIN_NAV_CLASS, CmsComponent::PARAMETER_SITEMAP);
            $sitemapItem = $cmsComponent->storage->getSitemapItemBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]);
            if (isset($request::$post[CmsComponent::POST_PARAMETER_TITLE], $request::$post[CmsComponent::POST_PARAMETER_TEMPLATE], $request::$post[CmsComponent::POST_PARAMETER_COMPONENT])) {
                $cmsComponent->storage->saveSitemapItem($request::$get[CmsComponent::GET_PARAMETER_SLUG], $request::$post);
                header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/sitemap');
                exit;
            }
            $cmsComponent->setParameter(CmsComponent::PARAMETER_SITEMAP_ITEM, $sitemapItem);
        } elseif ($relativeCmsUri == '/sitemap/delete' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
            $cmsComponent->storage->deleteSitemapItemBySlug($request::$get[CmsComponent::GET_PARAMETER_SLUG]);
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsComponent::PARAMETER_CMS_PREFIX) . '/sitemap');
            exit;
        }
    }
}