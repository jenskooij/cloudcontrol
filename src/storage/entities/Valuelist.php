<?php
/**
 * Created by: Jens
 * Date: 12-10-2017
 */

namespace CloudControl\Cms\storage\entities;


class Valuelist
{
    public $title;
    public $slug;
    public $pairs;

    /**
     * Valuelist constructor.
     * @param \stdClass $valuelist
     */
    public function __construct(\stdClass $valuelist)
    {
        $this->title = isset($valuelist->title) ? $valuelist->title : '';
        $this->slug = isset($valuelist->slug) ? $valuelist->slug : '';
        $this->pairs = isset($valuelist->pairs) ? $valuelist->pairs : new \stdClass();
    }

    public function get($key)
    {

        return isset($this->pairs->{$key}) ? $this->pairs->{$key} : '';
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return self::class . '(' . $this->title . ')';
    }


}