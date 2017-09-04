<?php
/**
 * Created by jensk on 17-3-2017.
 */

namespace CloudControl\Cms\storage\storage;

use CloudControl\Cms\storage\Document;
use CloudControl\Cms\storage\factories\DocumentFactory;
use CloudControl\Cms\storage\factories\DocumentFolderFactory;

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
        return $this->repository->getContentRepository()->getDocumentsByPath($this->repository, '/', $state);
    }

    public function getDocumentsWithState($folderPath = '/')
    {
        return $this->repository->getContentRepository()->getDocumentsWithState($this->repository, $folderPath);
    }

    /**
     * @return int
     */
    public function getTotalDocumentCount()
    {
        return $this->repository->getContentRepository()->getTotalDocumentCount();
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

        return $this->repository->getContentRepository()->getDocumentByPath($this->repository, $path, $state);
    }

    /**
     * @param $postValues
     * @param $state
     *
     * @return string path
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
        $this->repository->getContentRepository()->saveDocument($documentObject, $state);
        return $newPath;
    }

    /**
     * @param        $postValues
     * @param string $state
     * @return string path
     */
    public function addDocument($postValues, $state = 'unpublished')
    {
        $documentObject = DocumentFactory::createDocumentFromPostValues($postValues, new DocumentTypesStorage($this->repository));
        if ($postValues['path'] === '/') {
            $path = $postValues['path'] . $documentObject->slug;
        } else {
            $path = $postValues['path'] . '/' . $documentObject->slug;
        }

        $documentObject->path = $path;
        $this->repository->getContentRepository()->saveDocument($documentObject, $state);
        return $path;
    }

    /**
     * @param $slug
     */
    public function deleteDocumentBySlug($slug)
    {
        $path = '/' . $slug;
        $this->repository->getContentRepository()->deleteDocumentByPath($this->repository, $path);
    }

    /**
     * Returns the folder containing the document
     *
     * @param $path
     *
     * @return bool|\CloudControl\Cms\storage\Document
     * @throws \Exception
     */
    private function getDocumentContainerByPath($path)
    {
        return $this->repository->getContentRepository()->getDocumentContainerByPath($this->repository, $path);
    }

    public function getPublishedDocumentsNoFolders()
    {
        return $this->repository->getContentRepository()->getPublishedDocumentsNoFolders();
    }

    public function cleanPublishedDeletedDocuments()
    {
        $this->repository->getContentRepository()->cleanPublishedDeletedDocuments();
    }

    /**
     * Add new document in given path
     *
     * @param array $postValues
     *
     * @throws \Exception
     */
    public function addDocumentFolder($postValues)
    {
        $documentFolderObject = DocumentFolderFactory::createDocumentFolderFromPostValues($postValues);
        if ($postValues['path'] === '/') {
            $documentFolderObject->path = $postValues['path'] . $documentFolderObject->slug;
        } else {
            $documentFolderObject->path = $postValues['path'] . '/' . $documentFolderObject->slug;
        }
        $this->repository->getContentRepository()->saveDocument($documentFolderObject, 'published');
        $this->repository->getContentRepository()->saveDocument($documentFolderObject, 'unpublished');
    }

    /**
     * Delete a folder by its compound slug
     *
     * @param $slug
     *
     * @throws \Exception
     */
    public function deleteDocumentFolderBySlug($slug)
    {
        $path = '/' . $slug;
        $this->repository->getContentRepository()->deleteDocumentByPath($this->repository, $path);
        $this->repository->getContentRepository()->cleanPublishedDeletedDocuments();
    }

    /**
     * @param string $slug
     */
    public function publishDocumentBySlug($slug)
    {
        $path = '/' . $slug;
        $this->repository->getContentRepository()->publishDocumentByPath($path);
    }

    /**
     * @param string $slug
     */
    public function unpublishDocumentBySlug($slug)
    {
        $path = '/' . $slug;
        $this->repository->getContentRepository()->unpublishDocumentByPath($path);
    }

    /**
     * Retrieve a folder by its compound slug
     *
     * @param $slug
     *
     * @return mixed
     * @throws \Exception
     */
    public function getDocumentFolderBySlug($slug)
    {
        $path = '/' . $slug;

        return $this->repository->getContentRepository()->getDocumentByPath($this->repository, $path);
    }

}