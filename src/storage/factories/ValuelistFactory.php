<?php
/**
 * User: Jens
 * Date: 8-6-2017
 * Time: 14:39
 */

namespace CloudControl\Cms\storage\factories;


use CloudControl\Cms\cc\StringUtil;

class ValuelistFactory
{
    /**
     * Create a sitemap item from post values
     *
     * @param $postValues
     *
     * @return \stdClass
     * @throws \Exception
     */
    public static function createValuelistFromPostValues($postValues)
    {
        if (isset($postValues['title'])) {
            $valuelistObject = new \stdClass();
            $valuelistObject->title = $postValues['title'];
            $valuelistObject->slug = StringUtil::slugify($postValues['title']);
            $valuelistObject->pairs = new \stdClass();
            if (isset($postValues['keys'], $postValues['values'])) {
                foreach ($postValues['keys'] as $key => $value) {
                    $valuelistObject->pairs->$value = $postValues['values'][$key];
                }
            }
            $object_vars = get_object_vars($valuelistObject->pairs);
            ksort($object_vars);
            $valuelistObject->pairs = (object)$object_vars;

            return $valuelistObject;
        } else {
            throw new \Exception('Trying to create valuelist with invalid data.');
        }
    }

}