<?php
/**
 * Created by jensk on 12-10-2017.
 */

namespace CloudControl\Cms\services\fileservice;


use CloudControl\Cms\cc\Request;

class File
{
    protected $filePath;
    public $file;
    public $type;
    public $size;

    public function __construct(\stdClass $file, $filePath = 'files')
    {
        $this->file = $file->file;
        $this->type = $file->type;
        $this->size = $file->size;
        $this->filePath = $filePath;
    }

    public function __toString()
    {
        return Request::$subfolders . $this->filePath . '/' . $this->file;
    }

}