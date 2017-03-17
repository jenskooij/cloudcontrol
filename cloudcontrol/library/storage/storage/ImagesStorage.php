<?php
/**
 * Created by jensk on 17-3-2017.
 */

namespace library\storage\storage;


use library\images\ImageResizer;
use library\storage\factories\ImageFactory;

class ImagesStorage extends Storage
{
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
		$destinationPath = realpath(__DIR__ . '/../../../www/images/');

		$filename = $this->validateFilename($postValues['name'], $destinationPath);
		$destination = $destinationPath . '/' . $filename;

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
		$destinationPath = realpath(__DIR__ . '/../../../www/images/');

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
	 * @return \library\storage\storage\ImageSetStorage
	 */
	private function getImageSet()
	{
		if (!$this->imageSet instanceof ImageSetStorage) {
			$this->imageSet = new ImageSetStorage($this->repository);
		}
		return $this->imageSet;
	}
}