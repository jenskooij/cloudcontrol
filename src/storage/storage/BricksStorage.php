<?php
/**
 * Created by jensk on 17-3-2017.
 */

namespace CloudControl\Cms\storage\storage;


use CloudControl\Cms\storage\factories\BrickFactory;

class BricksStorage extends AbstractStorage
{
	/**
	 * @return array
	 */
	public function getBricks()
	{
		return $this->repository->bricks;
	}

	/**
	 * Add a brick
	 *
	 * @param $postValues
	 *
	 * @throws \Exception
	 */
	public function addBrick($postValues)
	{
		$brickObject = BrickFactory::createBrickFromPostValues($postValues);

		$bricks = $this->repository->bricks;
		$bricks[] = $brickObject;
		$this->repository->bricks = $bricks;

		$this->save();
	}

	/**
	 * Get a brick by its slug
	 *
	 * @param $slug
	 *
	 * @return \stdClass
	 */
	public function getBrickBySlug($slug)
	{
		$bricks = $this->repository->bricks;
		foreach ($bricks as $brick) {
			if ($brick->slug == $slug) {
				return $brick;
			}
		}

		return null;
	}

	/**
	 * Save changes to a brick
	 *
	 * @param $slug
	 * @param $postValues
	 *
	 * @throws \Exception
	 */
	public function saveBrick($slug, $postValues)
	{
		$brickObject = BrickFactory::createBrickFromPostValues($postValues);

		$bricks = $this->repository->bricks;
		foreach ($bricks as $key => $brick) {
			if ($brick->slug == $slug) {
				$bricks[$key] = $brickObject;
			}
		}
		$this->repository->bricks = $bricks;
		$this->save();
	}

	/**
	 * Delete a brick by its slug
	 *
	 * @param $slug
	 *
	 * @throws \Exception
	 */
	public function deleteBrickBySlug($slug)
	{
		$bricks = $this->repository->bricks;
		foreach ($bricks as $key => $brickObject) {
			if ($brickObject->slug == $slug) {
				unset($bricks[$key]);
			}
		}

		$bricks = array_values($bricks);
		$this->repository->bricks = $bricks;
		$this->save();
	}
}