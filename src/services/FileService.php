<?php
/**
 * Created by jensk on 12-10-2017.
 */

namespace CloudControl\Cms\services;
use CloudControl\Cms\services\fileservice\File;
use CloudControl\Cms\storage\Storage;

/**
 * Class FileService
 * Singleton
 *
 * @package CloudControl\Cms\services
 */
class FileService
{
    private static $instance;
    /**
     * @var Storage
     */
    protected $storage;

    protected function __construct()
    {}

    /**
     * @return FileService
     */
    public static function getInstance()
    {
        if (!self::$instance instanceof FileService) {
            self::$instance = new FileService();
        }
        return self::$instance;
    }

    public static function get($filePath)
    {
        $instance = self::getInstance();
        return $instance->getFileByPath($filePath);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return print_r(self::$instance, true);
    }

    protected function getFileByPath($filePath)
    {
        $file = $this->storage->getFiles()->getFileByName($filePath);
        return new File($file);
    }

    public function init(Storage $storage)
    {
        $this->storage = $storage;
    }


}