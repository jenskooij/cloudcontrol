<?php
/**
 * User: jensk
 * Date: 13-3-2017
 * Time: 16:56
 */

namespace CloudControl\Cms\storage\factories;


use CloudControl\Cms\cc\StringUtil;

abstract class AbstractBricksFactory
{
	/**
	 * @param $postValues
	 * @param $title
	 * @param $fieldType
	 *
	 * @return \stdClass
	 */
	protected static function createFieldObject($postValues, $title, $fieldType)
	{
		$fieldObject = new \stdClass();
		$fieldObject->title = $title;
		$fieldObject->slug = StringUtil::slugify($title);
		$fieldObject->type = $postValues['fieldTypes'][$fieldType];
		$fieldObject->required = ($postValues['fieldRequired'][$fieldType] === 'true');
		$fieldObject->multiple = ($postValues['fieldMultiple'][$fieldType] === 'true');

		return $fieldObject;
	}
}