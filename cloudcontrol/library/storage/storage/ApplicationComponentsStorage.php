<?php
/**
 * Created by jensk on 17-3-2017.
 */

namespace library\storage\storage;


use library\storage\factories\ApplicationComponentFactory;

class ApplicationComponentsStorage extends AbstractStorage
{
	/**
	 * @return array
	 */
	public function getApplicationComponents()
	{
		return $this->repository->applicationComponents;
	}

	/**
	 * @param $postValues
	 */
	public function addApplicationComponent($postValues)
	{
		$applicationComponent = ApplicationComponentFactory::createApplicationComponentFromPostValues($postValues);
		$applicationComponents = $this->repository->applicationComponents;
		$applicationComponents[] = $applicationComponent;
		$this->repository->applicationComponents = $applicationComponents;

		$this->save();
	}

	/**
	 * @param $slug
	 *
	 * @return mixed|null
	 */
	public function getApplicationComponentBySlug($slug)
	{
		$applicationComponents = $this->getApplicationComponents();
		foreach ($applicationComponents as $applicationComponent) {
			if ($applicationComponent->slug == $slug) {
				return $applicationComponent;
			}
		}

		return null;
	}

	/**
	 * @param $slug
	 * @param $postValues
	 */
	public function saveApplicationComponent($slug, $postValues)
	{
		$newApplicationComponent = ApplicationComponentFactory::createApplicationComponentFromPostValues($postValues);

		$applicationComponents = $this->getApplicationComponents();
		foreach ($applicationComponents as $key => $applicationComponent) {
			if ($applicationComponent->slug == $slug) {
				$applicationComponents[$key] = $newApplicationComponent;
			}
		}
		$this->repository->applicationComponents = $applicationComponents;
		$this->save();
	}

	/**
	 * @param $slug
	 */
	public function deleteApplicationComponentBySlug($slug)
	{
		$applicationComponents = $this->getApplicationComponents();
		foreach ($applicationComponents as $key => $applicationComponent) {
			if ($applicationComponent->slug == $slug) {
				unset($applicationComponents[$key]);
			}
		}
		$applicationComponents = array_values($applicationComponents);
		$this->repository->applicationComponents = $applicationComponents;
		$this->save();
	}
}