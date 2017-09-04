<?php
/**
 * Created by jensk on 17-3-2017.
 */

namespace CloudControl\Cms\components;


use CloudControl\Cms\storage\Storage;

class DocumentFolderComponent extends BaseComponent
{
    const PARAMETER_DOCUMENT_FOLDER_PATH = 'documentFolderPath';
    const PARAMETER_DOCUMENT_FOLDER_PARAMETER_NAME = 'documentFolderParameter';

    protected $documentFolderParameterName = 'folder';
    protected $documentFolderPath;

    /**
     * @param Storage $storage
     *
     * @return mixed|void
     * @throws \Exception
     */
    public function run(Storage $storage)
    {
        parent::run($storage);

        $this->checkParameters();

        $this->parameters[$this->documentFolderParameterName] = $this->storage->getDocuments()->getDocumentFolderBySlug($this->documentFolderPath);
    }


    /**
     * Checks to see if any parameters were defined in the cms and acts according
     */
    private function checkParameters()
    {
        if (isset($this->parameters[self::PARAMETER_DOCUMENT_FOLDER_PATH])) {
            $this->documentFolderPath = $this->parameters[self::PARAMETER_DOCUMENT_FOLDER_PATH];
        }

        if (isset($this->parameters[self::PARAMETER_DOCUMENT_FOLDER_PARAMETER_NAME])) {
            $this->documentFolderParameterName = $this->parameters[self::PARAMETER_DOCUMENT_FOLDER_PARAMETER_NAME];
        }
    }
}