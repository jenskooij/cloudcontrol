<?php
/**
 * Created by jensk on 17-3-2017.
 */

namespace CloudControl\Cms\storage\storage;


use CloudControl\Cms\images\ImageResizer;
use CloudControl\Cms\storage\factories\ImageFactory;

class ImagesStorage extends AbstractStorage
{
    protected $imagesDir;

    public function __construct($repository, $imagesDir)
    {
        parent::__construct($repository);
        $this->imagesDir = $imagesDir;
    }


    /**
     * @var ImageSetStorage
     */
    protected $imageSet;

    /**
     * Get all images
     *
     * @return array
     */
    public function getImages()
    {
        return $this->repository->images;
    }

    /**
     * @param $postValues
     *
     * @throws \Exception
     */
    public function addImage($postValues)
    {
        $destinationPath = $this->getDestinationPath();

        $filename = $this->validateFilename($postValues['name'], $destinationPath);
        $destination = $destinationPath . DIRECTORY_SEPARATOR . $filename;

        if ($postValues['error'] != '0') {
            throw new \Exception('Error uploading file. Error code: ' . $postValues['error']);
        }

        if (move_uploaded_file($postValues['tmp_name'], $destination)) {
            $imageResizer = new ImageResizer($this->getImageSet()->getImageSet());
            $fileNames = $imageResizer->applyImageSetToImage($destination);
            $fileNames['original'] = $filename;
            $imageObject = ImageFactory::createImageFromPostValues($postValues, $filename, $fileNames);

            $images = $this->repository->images;
            $images[] = $imageObject;
            $this->repository->images = $images;

            $this->save();
        } else {
            throw new \Exception('Error moving uploaded file');
        }
    }

    /**
     * Delete image by name
     * @param $filename
     */
    public function deleteImageByName($filename)
    {
        $destinationPath = $this->getDestinationPath();

        $images = $this->getImages();

        foreach ($images as $key => $image) {
            if ($image->file == $filename) {
                foreach ($image->set as $imageSetFilename) {
                    $destination = $destinationPath . '/' . $imageSetFilename;
                    if (file_exists($destination)) {
                        unlink($destination);
                    } else {
                        dump($destination);
                    }
                }
                unset($images[$key]);
            }
        }

        $this->repository->images = $images;
        $this->save();
    }

    /**
     * @param $filename
     *
     * @return null
     */
    public function getImageByName($filename)
    {
        $images = $this->getImages();
        foreach ($images as $image) {
            if ($image->file == $filename) {
                return $image;
            }
        }

        return null;
    }

    /**
     * @return \CloudControl\Cms\storage\storage\ImageSetStorage
     */
    private function getImageSet()
    {
        if (!$this->imageSet instanceof ImageSetStorage) {
            $this->imageSet = new ImageSetStorage($this->repository);
        }
        return $this->imageSet;
    }

    /**
     * @return bool|string
     */
    private function getDestinationPath()
    {
        $destinationPath = realpath($this->imagesDir . DIRECTORY_SEPARATOR);
        return $destinationPath;
    }
}