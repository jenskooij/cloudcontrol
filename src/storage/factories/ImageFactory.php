<?php
/**
 * Created by jensk on 17-3-2017.
 */

namespace CloudControl\Cms\storage\factories;


use CloudControl\Cms\storage\entities\Image;

class ImageFactory
{
    /**
     * @param $postValues
     * @param $filename
     * @param $fileNames
     *
     * @return Image
     */
    public static function createImageFromPostValues($postValues, $filename, $fileNames)
    {
        $imageObject = new \stdClass();
        $imageObject->file = $filename;
        $imageObject->type = $postValues['type'];
        $imageObject->size = $postValues['size'];
        $imageObject->set = $fileNames;

        return new Image($imageObject);
    }
}