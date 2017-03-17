<?php
/**
 * User: jensk
 * Date: 17-3-2017
 * Time: 10:29
 */

namespace library\storage\storage;


use library\storage\factories\SitemapItemFactory;

class SitemapStorage extends AbstractStorage
{
	/**
	 * @return array
	 */
	public function getSitemap()
	{
		return $this->repository->sitemap;
	}

	/**
	 * Add a sitemap item
	 *
	 * @param $postValues
	 *
	 * @throws \Exception
	 */
	public function addSitemapItem($postValues)
	{
		$sitemapObject = SitemapItemFactory::createSitemapItemFromPostValues($postValues);
		$sitemap = $this->repository->sitemap;
		$sitemap[] = $sitemapObject;
		$this->repository->sitemap = $sitemap;
		$this->save();
	}

	/**
	 * Save changes to a sitemap item
	 *
	 * @param $slug
	 * @param $postValues
	 *
	 * @throws \Exception
	 */
	public function saveSitemapItem($slug, $postValues)
	{
		$sitemapObject = SitemapItemFactory::createSitemapItemFromPostValues($postValues);

		$sitemap = $this->repository->sitemap;
		foreach ($sitemap as $key => $sitemapItem) {
			if ($sitemapItem->slug == $slug) {
				$sitemap[$key] = $sitemapObject;
			}
		}
		$this->repository->sitemap = $sitemap;
		$this->save();
	}

	/**
	 * Delete a sitemap item by its slug
	 *
	 * @param $slug
	 *
	 * @throws \Exception
	 */
	public function deleteSitemapItemBySlug($slug)
	{
		$sitemap = $this->repository->sitemap;
		foreach ($sitemap as $key => $sitemapItem) {
			if ($sitemapItem->slug == $slug) {
				unset($sitemap[$key]);
			}
		}
		$sitemap = array_values($sitemap);
		$this->repository->sitemap = $sitemap;
		$this->save();
	}

	/**
	 * Save changes to a sitemap item
	 *
	 * @param $postValues
	 *
	 * @throws \Exception
	 */
	public function saveSitemap($postValues)
	{
		if (isset($postValues['sitemapitem']) && is_array($postValues['sitemapitem'])) {
			$sitemap = array();
			foreach ($postValues['sitemapitem'] as $sitemapItem) {
				$sitemapItemObject = json_decode($sitemapItem);
				if (isset($sitemapItemObject->object)) {
					unset($sitemapItemObject->object);
				}
				$sitemap[] = $sitemapItemObject;
			}
			$this->repository->sitemap = $sitemap;
			$this->save();
		}
	}

	/**
	 * Get a sitemap item by its slug
	 *
	 * @param $slug
	 *
	 * @return mixed
	 */
	public function getSitemapItemBySlug($slug)
	{
		$sitemap = $this->repository->sitemap;
		foreach ($sitemap as $sitemapItem) {
			if ($sitemapItem->slug == $slug) {
				return $sitemapItem;
			}
		}

		return null;
	}
}