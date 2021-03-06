<?php
/**
 * User: Jens
 * Date: 15-6-2017
 * Time: 15:06
 */

namespace CloudControl\Cms\components;


use CloudControl\Cms\storage\Storage;

class NotFoundComponent extends CachableBaseComponent
{
    protected $template = '404';

    public function run(Storage $storage)
    {
        parent::run($storage);
    }

    protected function set404Header()
    {
        header("HTTP/1.0 404 Not Found");
    }

    protected function set404Template($template = '404')
    {
        $this->template = $template;
    }

}