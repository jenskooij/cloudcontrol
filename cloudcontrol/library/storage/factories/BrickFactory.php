<?php
/**
 * User: jensk
 * Date: 13-3-2017
 * Time: 16:58
 */

namespace library\storage\factories;


class BrickFactory extends AbstractBricksFactory
{
	/**
	 * Create a brick from post values
	 *
	 * @param $postValues
	 *
	 * @return \stdClass
	 * @throws \Exception
	 */
	public static function createBrickFromPostValues($postValues)
	{
		if (isset($postValues['title'])) {
			$brickObject = new \stdClass();
			$brickObject->title = $postValues['title'];
			$brickObject->slug = slugify($postValues['title']);
			$brickObject->fields = array();
			if (isset($postValues['fieldTitles'], $postValues['fieldTypes'], $postValues['fieldRequired'], $postValues['fieldMultiple'])) {
				foreach ($postValues['fieldTitles'] as $key => $value) {
					$fieldObject = self::createFieldObject($postValues, $value, $key);

					$brickObject->fields[] = $fieldObject;
				}
			}

			return $brickObject;
		} else {
			throw new \Exception('Trying to create document type with invalid data.');
		}
	}
}