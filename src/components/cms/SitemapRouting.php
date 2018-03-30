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

class SitemapRouting extends CmsRouting
{

    protected static $routes = array(
        '/sitemap' => 'overviewRoute',
        '/sitemap/new' => 'newRoute',
        '/sitemap/edit' => 'editRoute',
        '/sitemap/delete' => 'deleteRoute',
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

    /**
     * @param Request $request
     * @param CmsComponent $cmsComponent
     * @throws \Exception
     */
    protected function overviewRoute($request, $cmsComponent)
    {
        $cmsComponent->subTemplate = 'sitemap';
        if (isset($request::$post[CmsConstants::POST_PARAMETER_SAVE])) {
            $cmsComponent->storage->getSitemap()->saveSitemap($request::$post);
        }
        $cmsComponent->setParameter(CmsConstants::PARAMETER_MAIN_NAV_CLASS, CmsConstants::PARAMETER_SITEMAP);
        $cmsComponent->setParameter(CmsConstants::PARAMETER_SITEMAP,
            $cmsComponent->storage->getSitemap()->getSitemap());
    }

    /**
     * @param Request $request
     * @param CmsComponent $cmsComponent
     * @throws \Exception
     */
    protected function newRoute($request, $cmsComponent)
    {
        $cmsComponent->subTemplate = 'sitemap/form';
        $cmsComponent->setParameter(CmsConstants::PARAMETER_MAIN_NAV_CLASS, CmsConstants::PARAMETER_SITEMAP);
        if (isset($request::$post[CmsConstants::POST_PARAMETER_TITLE], $request::$post[CmsConstants::POST_PARAMETER_TEMPLATE], $request::$post[CmsConstants::POST_PARAMETER_COMPONENT])) {
            $cmsComponent->storage->getSitemap()->addSitemapItem($request::$post);
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/sitemap');
            exit;
        }
    }

    /**
     * @param Request $request
     * @param CmsComponent $cmsComponent
     * @throws \Exception
     */
    protected function editRoute($request, $cmsComponent)
    {
        $cmsComponent->subTemplate = 'sitemap/form';
        $cmsComponent->setParameter(CmsConstants::PARAMETER_MAIN_NAV_CLASS, CmsConstants::PARAMETER_SITEMAP);
        $sitemapItem = $cmsComponent->storage->getSitemap()->getSitemapItemBySlug($request::$get[CmsConstants::GET_PARAMETER_SLUG]);
        if (isset($request::$post[CmsConstants::POST_PARAMETER_TITLE], $request::$post[CmsConstants::POST_PARAMETER_TEMPLATE], $request::$post[CmsConstants::POST_PARAMETER_COMPONENT])) {
            $cmsComponent->storage->getSitemap()->saveSitemapItem($request::$get[CmsConstants::GET_PARAMETER_SLUG],
                $request::$post);
            header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/sitemap');
            exit;
        }
        $cmsComponent->setParameter(CmsConstants::PARAMETER_SITEMAP_ITEM, $sitemapItem);
    }

    /**
     * @param Request $request
     * @param CmsComponent $cmsComponent
     * @throws \Exception
     */
    protected function deleteRoute($request, $cmsComponent)
    {
        $cmsComponent->storage->getSitemap()->deleteSitemapItemBySlug($request::$get[CmsConstants::GET_PARAMETER_SLUG]);
        header('Location: ' . $request::$subfolders . $cmsComponent->getParameter(CmsConstants::PARAMETER_CMS_PREFIX) . '/sitemap');
        exit;
    }
}