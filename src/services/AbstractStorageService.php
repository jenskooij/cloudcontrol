<?php
/**
 * Created by jensk on 18-5-2018.
 */

namespace CloudControl\Cms\services;


use CloudControl\Cms\storage\Storage;

abstract class AbstractStorageService
{
    protected $storage;

    public function init(Storage $storage)
    {
        $this->storage = $storage;
    }
}