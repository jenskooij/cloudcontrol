<?php
/**
 * Created by IntelliJ IDEA.
 * User: Jens
 * Date: 29-1-2017
 * Time: 16:30
 */

namespace library\components\cms;


use library\components\CmsComponent;

class SitemapRouting implements CmsRouting
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
			$this->overviewRoute($request, $cmsComponent);
        } elseif ($relativeCmsUri == '/sitemap/new') {
			$this->newRoute($request, $cmsComponent);
        } elseif ($relativeCmsUri == '/sitemap/edit' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
			$this->editRoute($request, $cmsComponent);
        } elseif ($relativeCmsUri == '/sitemap/delete' && isset($request::$get[CmsComponent::GET_PARAMETER_SLUG])) {
			$this->deleteRoute($request, $cmsComponent);
        }
    }

	/**
	 * @param $request
	 * @param CmsComponent $cmsComponent
	 */
	private function overviewRoute($request, $cmsComponent)
	{
		$cmsComponent->subTemplate = 'cms/sitemap';
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
		$cmsComponent->subTemplate = 'cms/sitemap/form';
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
		$cmsComponent->subTemplate = 'cms/sitemap/form';
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
}