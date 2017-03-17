<?php
/**
 * Created by jensk on 17-3-2017.
 */

namespace library\storage\storage;

use library\storage\factories\DocumentFactory;

class DocumentStorage extends AbstractStorage
{
	/**
	 * Get documents
	 *
	 * @return array
	 */
	public function getDocuments()
	{
		return $this->repository->getDocuments();
	}

	/**
	 * @return int
	 */
	public function getTotalDocumentCount()
	{
		return $this->repository->getTotalDocumentCount();
	}

	/**
	 * @param string $slug
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function getDocumentBySlug($slug)
	{
		$path = '/' . $slug;

		return $this->repository->getDocumentByPath($path);
	}

	/**
	 * @param $postValues
	 */
	public function saveDocument($postValues)
	{
		$oldPath = '/' . $postValues['path'];

		$container = $this->getDocumentContainerByPath($oldPath);
		$documentObject = DocumentFactory::createDocumentFromPostValues($postValues, new DocumentTypesStorage($this->repository));
		if ($container->path === '/') {
			$newPath = $container->path . $documentObject->slug;
		} else {
			$newPath = $container->path . '/' . $documentObject->slug;
		}
		$documentObject->path = $newPath;
		$this->repository->saveDocument($documentObject);
	}

	/**
	 * @param $postValues
	 */
	public function addDocument($postValues)
	{
		$documentObject = DocumentFactory::createDocumentFromPostValues($postValues, $this);
		if ($postValues['path'] === '/') {
			$documentObject->path = $postValues['path'] . $documentObject->slug;
		} else {
			$documentObject->path = $postValues['path'] . '/' . $documentObject->slug;
		}

		$this->repository->saveDocument($documentObject);
	}

	/**
	 * @param $slug
	 */
	public function deleteDocumentBySlug($slug)
	{
		$path = '/' . $slug;
		$this->repository->deleteDocumentByPath($path);
	}

	/**
	 * Returns the folder containing the document
	 *
	 * @param $path
	 *
	 * @return bool|\library\storage\Document
	 * @throws \Exception
	 */
	private function getDocumentContainerByPath($path)
	{
		return $this->repository->getDocumentContainerByPath($path);
	}
}