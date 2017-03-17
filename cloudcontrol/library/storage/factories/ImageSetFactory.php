<?php
/**
 * User: jensk
 * Date: 13-3-2017
 * Time: 17:05
 */

namespace library\storage\factories;


use library\cc\StringUtil;

class ImageSetFactory
{
	/**
	 * Ceate image set from post values
	 *
	 * @param $postValues
	 *
	 * @return \stdClass
	 * @throws \Exception
	 */
	public static function createImageSetFromPostValues($postValues)
	{
		if (isset($postValues['title'], $postValues['width'], $postValues['height'], $postValues['method'])) {
			$imageSetObject = new \stdClass();

			$imageSetObject->title = $postValues['title'];
			$imageSetObject->slug = StringUtil::slugify($postValues['title']);
			$imageSetObject->width = $postValues['width'];
			$imageSetObject->height = $postValues['height'];
			$imageSetObject->method = $postValues['method'];

			return $imageSetObject;
		} else {
			throw new \Exception('Trying to create image set with invalid data.');
		}
	}
}