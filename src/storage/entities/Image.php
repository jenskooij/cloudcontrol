<?php
/**
 * Created by jensk on 12-10-2017.
 */

namespace CloudControl\Cms\storage\entities;


use CloudControl\Cms\cc\Request;

/**
 * Class Image
 * @package CloudControl\Cms\services\imageservice
 */
class Image
{
    protected $imagePath;
    public $file;
    public $type;
    public $size;
    public $set;

    /**
     * Image constructor.
     * @param \stdClass $image
     * @param string $imagePath
     */
    public function __construct(\stdClass $image, $imagePath = 'images')
    {
        $this->file = $image->file;
        $this->type = $image->type;
        $this->size = $image->size;
        $this->set  = $image->set;
        $this->imagePath = $imagePath;
    }

    /**
     * @param string $imageVariant
     * @return string
     * @throws \Exception
     */
    public function get($imageVariant = 'original')
    {
        if (!isset($this->set->{$imageVariant})) {
            throw new \Exception('Image variant `' . $imageVariant . '` does not exist. Existing variants are ' . implode(', ', array_keys((array) $this->set)));
        }
        return Request::$subfolders . $this->imagePath . '/' . $this->set->{$imageVariant};
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return Request::$subfolders . $this->imagePath . '/' . $this->file;
    }
}