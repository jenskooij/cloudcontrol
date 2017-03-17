<?php
/**
 * Created by jensk on 17-3-2017.
 */

namespace library\storage\storage;


use library\storage\factories\ImageSetFactory;

class ImageSetStorage extends AbstractStorage
{

	/**
	 * @return mixed
	 */
	public function getImageSet()
	{
		return $this->repository->imageSet;
	}

	/**
	 * Get Image by slug
	 *
	 * @param $slug
	 *
	 * @return \stdClass
	 */
	public function getImageSetBySlug($slug)
	{
		$imageSet = $this->getImageSet();
		foreach ($imageSet as $set) {
			if ($set->slug == $slug) {
				return $set;
			}
		}

		return null;
	}

	/**
	 * Add image set
	 *
	 * @param $postValues
	 *
	 * @throws \Exception
	 */
	public function addImageSet($postValues)
	{
		$imageSetObject = ImageSetFactory::createImageSetFromPostValues($postValues);

		$imageSet = $this->repository->imageSet;
		$imageSet[] = $imageSetObject;
		$this->repository->imageSet = $imageSet;

		$this->save();
	}

	/**
	 * Save Image Set by it's slug
	 *
	 * @param $slug
	 * @param $postValues
	 *
	 * @throws \Exception
	 */
	public function saveImageSet($slug, $postValues)
	{
		$imageSetObject = ImageSetFactory::createImageSetFromPostValues($postValues);

		$imageSet = $this->repository->imageSet;
		foreach ($imageSet as $key => $set) {
			if ($set->slug == $slug) {
				$imageSet[$key] = $imageSetObject;
			}
		}
		$this->repository->imageSet = $imageSet;
		$this->save();
	}

	/**
	 * Delete Image Set by its slug
	 *
	 * @param $slug
	 *
	 * @throws \Exception
	 */
	public function deleteImageSetBySlug($slug)
	{
		$imageSet = $this->getImageSet();

		foreach ($imageSet as $key => $set) {
			if ($set->slug == $slug) {
				unset($imageSet[$key]);
			}
		}
		$imageSet = array_values($imageSet);
		$this->repository->imageSet = $imageSet;
		$this->save();
	}

	/**
	 * Get the image set with the smallest size
	 *
	 * @return \stdClass
	 */
	public function getSmallestImageSet()
	{
		$imageSet = $this->getImageSet();

		$returnSize = PHP_INT_MAX;
		$returnSet = null;

		foreach ($imageSet as $set) {
			$size = $set->width * $set->height;
			if ($size < $returnSize) {
				$returnSize = $size;
				$returnSet = $set;
			}
		}

		if ($returnSet === null) {
			$returnSet = new \stdClass();
			$returnSet->slug = 'original';
		}

		return $returnSet;
	}
}