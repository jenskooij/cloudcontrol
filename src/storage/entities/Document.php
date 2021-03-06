<?php
/**
 * User: Jens
 * Date: 17-2-2017
 * Time: 13:00
 */

namespace CloudControl\Cms\storage\entities;

use CloudControl\Cms\components\CmsComponent;
use CloudControl\Cms\storage\storage\DocumentStorage;
use CloudControl\Cms\util\DocumentSorter;

/**
 * Class Document
 * @package CloudControl\Cms\storage
 * @property array fields
 * @property array bricks
 * @property array dynamicBricks
 * @property array content
 * @property \PDO dbHandle
 * @property DocumentStorage documentStorage
 * @property boolean unpublishedChanges
 * @property \stdClass $documentContent Used in ApiComponent
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
    public $publicationDate;
    public $unpublishedChanges;
    protected $documentStorage;
    protected $fields;
    protected $bricks;
    protected $dynamicBricks;
    protected $content;

    protected $order = 'ASC';
    protected $orderByField;

    protected $dbHandle;

    private static $JSON_ENCODED_FIELDS = array('fields', 'bricks', 'dynamicBricks');

    public static $DOCUMENT_STATES = array('published', 'unpublished');

    /**
     * @param $name
     * @return array|mixed
     * @throws \Exception
     */
    public function __get($name)
    {
        if (in_array($name, self::$JSON_ENCODED_FIELDS, true)) {
            return $this->getJsonEncodedField($name);
        }

        if ($name === 'content') {
            return $this->getContent();
        }

        if ($name === 'dbHandle') {
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
        if (in_array($name, self::$JSON_ENCODED_FIELDS, true)) {
            $this->$name = json_encode($value);
        } elseif ($name === 'content') {
            // Dont do anything for now..
            return;
        }

        $this->$name = $value;
    }

    /**
     * @return array
     * @throws \Exception
     * @throws \RuntimeException
     */
    public function getContent()
    {
        if ($this->dbHandle === null) {
            throw new \RuntimeException('Document doesnt have a dbHandle handle. (path: ' . $this->path . ')');
        }

        if ($this->content === null) {
            $slug = substr($this->path, 1);
            if (CmsComponent::isCmsLoggedIn()) {
                $docs = $this->documentStorage->getDocumentsWithState($this->path);
            } else {
                $docs = $this->documentStorage->getDocumentsBySlug($slug);
            }

            if ($this->orderByField !== null) {
                $docs = $this->orderContentByField($docs);
            }

            $this->content = $docs;
        }

        return $this->content;
    }

    public function orderByField($fieldName, $order = 'ASC')
    {
        $this->orderByField = $fieldName;
        $this->order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
        return $this;
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
        $temp = preg_replace('@^O:8:"stdClass":@',
            'O:' . strlen(FieldContainer::class) . ':"' . FieldContainer::class . '":', $temp);
        return unserialize($temp);
    }

    /**
     * @param $name
     * @return array|mixed
     */
    private function getJsonEncodedField($name)
    {
        if (isset($this->$name) && is_string($this->$name)) {
            return $this->decodeJsonToFieldContainer($name);
        }

        return $this->getPropertyIfExists($name);
    }

    /**
     * @param array $docs
     * @return array
     */
    protected function orderContentByField($docs)
    {
        return DocumentSorter::sortDocumentsByField($docs, $this->orderByField, $this->order);
    }
}