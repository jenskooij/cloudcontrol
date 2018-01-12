<?php
/**
 * Created by jensk on 12-1-2018.
 */

namespace CloudControl\Cms\storage\entities;


class FieldContainer
{
    public function __get($property)
    {
        if (isset($this->$property)) {
            return $this->$property;
        } else {
            return array(
                0 => ''
            );
        }
    }
}