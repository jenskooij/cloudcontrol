<?php
/**
 * Created by: Jens
 * Date: 12-10-2017
 */

namespace CloudControl\Cms\storage\factories;


use CloudControl\Cms\storage\entities\File;

class FileFactory
{
    /**
     * @param $postValues
     * @param $filename
     * @return File
     *
     */
    public static function createFileFromPostValues($postValues, $filename)
    {
        $fileObject = new \stdClass();
        $fileObject->file = $filename;
        $fileObject->type = $postValues['type'];
        $fileObject->size = $postValues['size'];

        return new File($fileObject);
    }
}