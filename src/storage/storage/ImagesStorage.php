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

    /**
     * @param \CloudControl\Cms\storage\Repository $repository
     * @param string $imagesDir
     */
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
     * @return \CloudControl\Cms\storage\entities\Image
     * @throws \Exception
     */
    public function addImage($postValues)
    {
        $destinationPath = $this->getDestinationPath();

        $filename = $this->validateFilename($postValues['name'], $destinationPath);
        $destination = $destinationPath . DIRECTORY_SEPARATOR . $filename;

        if ('0' != $postValues['error']) {
            throw new \RuntimeException('Error uploading file. Error code: ' . $postValues['error']);
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

            return $imageObject;
        } else {
            throw new \RuntimeException('Error moving uploaded file');
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
                    }
                }
                unset($images[$key]);
            }
        }
        $images = array_values($images);
        $this->repository->images = $images;
        $this->save();
    }

    /**
     * @param $filename
     *
     * @return \stdClass|null
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