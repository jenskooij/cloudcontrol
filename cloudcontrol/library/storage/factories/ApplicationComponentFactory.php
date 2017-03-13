<?php
/**
 * User: jensk
 * Date: 13-3-2017
 * Time: 17:06
 */

namespace library\storage\factories;


class ApplicationComponentFactory
{
	/**
	 * @param $postValues
	 *
	 * @return \stdClass
	 * @throws \Exception
	 */
	public static function createApplicationComponentFromPostValues($postValues)
	{
		if (isset($postValues['title'], $postValues['component'])) {
			$applicationComponent = new \stdClass();
			$applicationComponent->title = $postValues['title'];
			$applicationComponent->slug = slugify($postValues['title']);
			$applicationComponent->component = $postValues['component'];
			$applicationComponent->parameters = new \stdClass();
			if (isset($postValues['parameterNames'], $postValues['parameterValues'])) {
				foreach ($postValues['parameterNames'] as $key => $value) {
					$applicationComponent->parameters->$value = $postValues['parameterValues'][$key];
				}
			}

			return $applicationComponent;
		} else {
			throw new \Exception('Trying to create application component with invalid data.');
		}
	}
}