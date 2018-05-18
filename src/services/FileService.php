<?php
/**
 * Created by jensk on 12-10-2017.
 */

namespace CloudControl\Cms\services;

use CloudControl\Cms\storage\entities\File;
use CloudControl\Cms\storage\Storage;

/**
 * Class FileService
 * Singleton
 *
 * @package CloudControl\Cms\services
 */
class FileService extends AbstractStorageService
{
    private static $instance;

    /**
     * FileService constructor.
     */
    protected function __construct()
    {
    }

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

    /**
     * @param $filePath
     * @return File
     */
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

    /**
     * @param $filePath
     * @return File
     */
    protected function getFileByPath($filePath)
    {
        $file = $this->storage->getFiles()->getFileByName($filePath);
        return $file === null ? null : new File($file);
    }


}