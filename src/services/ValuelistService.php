<?php
/**
 * Created by: Jens
 * Date: 12-10-2017
 */

namespace CloudControl\Cms\services;


use CloudControl\Cms\storage\entities\Valuelist;
use CloudControl\Cms\storage\Storage;

class ValuelistService extends AbstractStorageService
{
    private static $instance;

    /**
     * FileService constructor.
     */
    protected function __construct()
    {
    }

    /**
     * @return ValuelistService
     */
    public static function getInstance()
    {
        if (!self::$instance instanceof ValuelistService) {
            self::$instance = new ValuelistService();
        }
        return self::$instance;
    }

    /**
     * @param $slug
     * @return null|Valuelist
     */
    public static function get($slug)
    {
        $instance = self::getInstance();
        $valuelist = $instance->storage->getValuelists()->getValuelistBySlug($slug);
        return $valuelist === null ? null : new Valuelist($valuelist);

    }
}