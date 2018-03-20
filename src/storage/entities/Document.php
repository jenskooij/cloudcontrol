<?php
/**
 * User: Jens
 * Date: 17-2-2017
 * Time: 13:00
 */

namespace CloudControl\Cms\storage\entities;

use CloudControl\Cms\storage\storage\DocumentStorage;

/**
 * Class Document
 * @package CloudControl\Cms\storage
 * @property array fields
 * @property array bricks
 * @property array dynamicBricks
 * @property array content
 * @property-write \PDO dbHandle
 * @property-write DocumentStorage documentStorage
 * @property boolean unpublishedChanges
 */
class Document
{
    public $id;
    public $path;
    public $title;
    public $slug;
    public $type;
    public $documentType;
    public $documentTypeSlug;
    public $state;
    public $lastModificationDate;
    public $creationDate;
    public $lastModifiedBy;
    protected $fields;
    protected $bricks;
    protected $dynamicBricks;
    protected $content;

    protected $dbHandle;

    protected $jsonEncodedFields = array('fields', 'bricks', 'dynamicBricks');
    protected $orderableFields = array('title', 'slug', 'type', 'documentType', 'documentTypeSlug', 'state', 'lastModificationDate', 'creationDate', 'lastModifiedBy');

    public static $DOCUMENT_STATES = array('published', 'unpublished');

    /**
     * @param $name
     * @return array|mixed
     * @throws \Exception
     */
    public function __get($name)
    {
        if (in_array($name, $this->jsonEncodedFields, true)) {
            if (isset($this->$name) && is_string($this->$name)) {
                return $this->decodeJsonToFieldContainer($name);
            }

            return $this->getPropertyIfExists($name);
        }

        if ($name === 'content') {
            if ($this->dbHandle === null) {
                throw new \RuntimeException('Document doesnt have a dbHandle handle. (path: ' . $this->path . ')');
            }

            if ($this->content === null) {
                return $this->getContent();
            }
        } elseif ($name === 'dbHandle') {
            throw new \RuntimeException('Trying to get protected property repository.');
        }
        return $this->getPropertyIfExists($name);
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        if (in_array($name, $this->jsonEncodedFields, true)) {
            $this->$name = json_encode($value);
        } elseif ($name === 'content') {
            // Dont do anything for now..
            return;
        }

        $this->$name = $value;
    }

    /**
     * @return array
     */
    public function getContent()
    {
        if (empty($this->content)) {
            $docs = $this->documentStorage->getDocumentsWithState($this->path);
            $this->content = $docs;
        }

        return $this->content;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return 'Document:' . $this->title;
    }

    /**
     * @param $name
     * @return array
     */
    private function getPropertyIfExists($name)
    {
        return isset($this->$name) ? $this->$name : array('');
    }

    /**
     * @param $name
     * @return mixed
     */
    private function decodeJsonToFieldContainer($name)
    {
        $stdObj = json_decode($this->$name);
        $temp = serialize($stdObj);
        $temp = preg_replace('@^O:8:"stdClass":@', 'O:' . strlen(FieldContainer::class) . ':"' . FieldContainer::class . '":', $temp);
        return unserialize($temp);
    }


}