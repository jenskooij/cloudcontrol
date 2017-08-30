<?php
/**
 * Created by jensk on 17-3-2017.
 */

namespace CloudControl\Cms\storage\storage;


use CloudControl\Cms\storage\factories\DocumentTypeFactory;

class DocumentTypesStorage extends AbstractStorage
{
    /**
     * @var BricksStorage
     */
    protected $bricks;

    /**
     * @return array
     */
    public function getDocumentTypes()
    {
        return $this->repository->documentTypes;
    }

    /**
     * Add a document type from post values
     *
     * @param $postValues
     *
     * @throws \Exception
     */
    public function addDocumentType($postValues)
    {
        $documentTypeObject = DocumentTypeFactory::createDocumentTypeFromPostValues($postValues);

        $documentTypes = $this->repository->documentTypes;
        $documentTypes[] = $documentTypeObject;
        $this->repository->documentTypes = $documentTypes;

        $this->save();
    }

    /**
     * Delete document type
     *
     * @param $slug
     *
     * @throws \Exception
     */
    public function deleteDocumentTypeBySlug($slug)
    {
        $documentTypes = $this->repository->documentTypes;
        foreach ($documentTypes as $key => $documentTypeObject) {
            if ($documentTypeObject->slug == $slug) {
                unset($documentTypes[$key]);
            }
        }
        $documentTypes = array_values($documentTypes);
        $this->repository->documentTypes = $documentTypes;
        $this->save();
    }

    /**
     * Get document type by its slug
     *
     * @param      $slug
     * @param bool $getBricks
     *
     * @return mixed
     */
    public function getDocumentTypeBySlug($slug, $getBricks = false)
    {
        $documentTypes = $this->repository->documentTypes;
        foreach ($documentTypes as $documentType) {
            if ($documentType->slug == $slug) {
                if ($getBricks === true) {
                    foreach ($documentType->bricks as $key => $brick) {
                        $brickStructure = $this->getBricks()->getBrickBySlug($brick->brickSlug);
                        $documentType->bricks[$key]->structure = $brickStructure;
                    }
                    foreach ($documentType->dynamicBricks as $key => $brickSlug) {
                        $brickStructure = $this->getBricks()->getBrickBySlug($brickSlug);
                        $documentType->dynamicBricks[$key] = $brickStructure;
                    }
                }

                return $documentType;
            }
        }

        return null;
    }

    /**
     * Save changes to a document type
     *
     * @param $slug
     * @param $postValues
     *
     * @throws \Exception
     */
    public function saveDocumentType($slug, $postValues)
    {
        $documentTypeObject = DocumentTypeFactory::createDocumentTypeFromPostValues($postValues);

        $documentTypes = $this->repository->documentTypes;
        foreach ($documentTypes as $key => $documentType) {
            if ($documentType->slug == $slug) {
                $documentTypes[$key] = $documentTypeObject;
            }
        }
        $this->repository->documentTypes = $documentTypes;
        $this->save();
    }

    private function getBricks()
    {
        if (!$this->bricks instanceof BricksStorage) {
            $this->bricks = new BricksStorage($this->repository);
        }
        return $this->bricks;
    }
}