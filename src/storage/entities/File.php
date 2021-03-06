<?php
/**
 * Created by jensk on 12-10-2017.
 */

namespace CloudControl\Cms\storage\entities;


use CloudControl\Cms\cc\Request;

/**
 * Class File
 * @package CloudControl\Cms\services\fileservice
 */
class File
{
    protected $filePath;

    public $file;
    public $type;
    public $size;

    /**
     * File constructor.
     * @param \stdClass $file
     * @param string $filePath
     */
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

    /**
     * @param string $filePath
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
    }

}