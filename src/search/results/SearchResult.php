<?php
/**
 * User: jensk
 * Date: 3-3-2017
 * Time: 16:56
 */

namespace CloudControl\Cms\search\results;


use CloudControl\Cms\storage\entities\Document;
use CloudControl\Cms\storage\Storage;

class SearchResult
{
    /**
     * @var string
     */
    public $documentPath;
    /**
     * @var array
     */
    public $matchingTokens;
    /**
     * @var float
     */
    public $score;

    protected $document;
    /**
     * @var Storage
     */
    protected $storage;

    /**
     * @return Document
     * @throws \Exception
     */
    public function getDocument()
    {
        if ($this->document instanceof Document) {
            return $this->document;
        } else {
            $this->document = $this->storage->getDocuments()->getDocumentBySlug(substr($this->documentPath, 1));
            $this->document->dbHandle = $this->storage->getContentDbHandle();
            $this->document->documentStorage = $this->storage->getRepository();

            return $this->document;
        }
    }

    public function setStorage($storage)
    {
        $this->storage = $storage;
    }
}