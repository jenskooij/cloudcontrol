<?php
/**
 * User: Jens
 * Date: 15-6-2017
 * Time: 15:06
 */

namespace library\components;


use library\storage\Storage;

class NotFoundComponent extends BaseComponent
{
    public function run(Storage $storage)
    {
        parent::run($storage);
        header("HTTP/1.0 404 Not Found");
    }

}