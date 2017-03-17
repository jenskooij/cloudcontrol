<?php
/**
 * Created by jensk on 17-3-2017.
 */

namespace library\storage\storage;


class ImageSetStorage extends Storage
{

	/**
	 * @return mixed
	 */
	public function getImageSet()
	{
		return $this->repository->imageSet;
	}
}