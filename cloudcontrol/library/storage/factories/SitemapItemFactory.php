<?php
/**
 * User: jensk
 * Date: 13-3-2017
 * Time: 17:04
 */

namespace library\storage\factories;


use library\cc\StringUtil;

class SitemapItemFactory
{
	/**
	 * Create a sitemap item from post values
	 *
	 * @param $postValues
	 *
	 * @return \stdClass
	 * @throws \Exception
	 */
	public static function createSitemapItemFromPostValues($postValues)
	{
		if (isset($postValues['title'], $postValues['url'], $postValues['component'], $postValues['template'])) {
			$sitemapObject = new \stdClass();
			$sitemapObject->title = $postValues['title'];
			$sitemapObject->slug = StringUtil::slugify($postValues['title']);
			$sitemapObject->url = $postValues['url'];
			$sitemapObject->component = $postValues['component'];
			$sitemapObject->template = $postValues['template'];
			$sitemapObject->regex = isset($postValues['regex']);
			$sitemapObject->parameters = new \stdClass();
			if (isset($postValues['parameterNames'], $postValues['parameterValues'])) {
				foreach ($postValues['parameterNames'] as $key => $value) {
					$sitemapObject->parameters->$value = $postValues['parameterValues'][$key];
				}
			}

			return $sitemapObject;
		} else {
			throw new \Exception('Trying to create sitemap item with invalid data.');
		}
	}
}