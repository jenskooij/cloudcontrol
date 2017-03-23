<?php
/**
 * Created by jensk on 17-3-2017.
 */

namespace library\storage\storage;

use library\storage\Document;
use library\storage\factories\DocumentFactory;

class DocumentStorage extends AbstractStorage
{
	/**
	 * Get documents
	 *
	 * @param string $state
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function getDocuments($state = 'published')
	{
		if (!in_array($state, Document::$DOCUMENT_STATES)) {
			throw new \Exception('Unsupported document state: ' . $state);
		}
		return $this->repository->getDocuments($state);
	}

	public function getDocumentsWithState($folderPath = '/')
	{
		return $this->repository->getDocumentsWithState($folderPath);
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
	 * @param string $state
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function getDocumentBySlug($slug, $state = 'published')
	{
		if (!in_array($state, Document::$DOCUMENT_STATES)) {
			throw new \Exception('Unsupported document state: ' . $state);
		}
		$path = '/' . $slug;

		return $this->repository->getDocumentByPath($path, $state);
	}

	/**
	 * @param $postValues
	 * @param $state
	 *
	 * @throws \Exception
	 */
	public function saveDocument($postValues, $state = 'unpublished')
	{
		if (!in_array($state, Document::$DOCUMENT_STATES)) {
			throw new \Exception('Unsupported document state: ' . $state);
		}
		$oldPath = '/' . $postValues['path'];

		$container = $this->getDocumentContainerByPath($oldPath);
		$documentObject = DocumentFactory::createDocumentFromPostValues($postValues, new DocumentTypesStorage($this->repository));
		if ($container->path === '/') {
			$newPath = $container->path . $documentObject->slug;
		} else {
			$newPath = $container->path . '/' . $documentObject->slug;
		}
		$documentObject->path = $newPath;
		$this->repository->saveDocument($documentObject, $state);
	}

	/**
	 * @param        $postValues
	 * @param string $state
	 */
	public function addDocument($postValues, $state = 'unpublished')
	{
		$documentObject = DocumentFactory::createDocumentFromPostValues($postValues, new DocumentTypesStorage($this->repository));
		if ($postValues['path'] === '/') {
			$documentObject->path = $postValues['path'] . $documentObject->slug;
		} else {
			$documentObject->path = $postValues['path'] . '/' . $documentObject->slug;
		}

		$this->repository->saveDocument($documentObject, $state);
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

	public function getPublishedDocumentsNoFolders()
	{
		return $this->repository->getPublishedDocumentsNoFolders();
	}

	public function cleanPublishedDeletedDocuments()
	{
		$this->repository->cleanPublishedDeletedDocuments();
	}

}