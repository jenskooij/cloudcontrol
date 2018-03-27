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
        $this->title = $valuelist->title;
        $this->slug = $valuelist->slug;
        $this->pairs = $valuelist->pairs;
    }

    public function get($key)
    {
        return $this->pairs->{$key};
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return self::class . '(' . $this->title . ')';
    }


}