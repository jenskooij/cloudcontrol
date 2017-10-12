<?php
/**
 * Created by jensk on 12-10-2017.
 */

namespace CloudControl\Cms\services;


use CloudControl\Cms\storage\entities\Image;
use CloudControl\Cms\storage\Storage;

/**
 * Class ImageService
 * Singleton
 * @package CloudControl\Cms\services
 */
class ImageService
{
    private static $instance;
    /**
     * @var Storage
     */
    protected $storage;

    /**
     * ImageService constructor.
     */
    protected function __construct()
    {}

    /**
     * @return ImageService
     */
    public static function getInstance()
    {
        if (!self::$instance instanceof ImageService) {
            self::$instance = new ImageService();
        }
        return self::$instance;
    }

    /**
     * @param $imagePath
     * @return Image
     */
    public static function get($imagePath)
    {
        $instance = self::getInstance();
        return $instance->getImageByPath($imagePath);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) print_r(self::$instance, true);
    }

    /**
     * @param $imagePath
     * @return Image
     */
    protected function getImageByPath($imagePath)
    {
        $image = $this->storage->getImages()->getImageByName($imagePath);
        return new Image($image);
    }

    /**
     * @param Storage $storage
     */
    public function init(Storage $storage)
    {
        $this->storage = $storage;
    }
}