<?php
/**
 * User: Jens
 * Date: 17-2-2017
 * Time: 13:00
 */

namespace library\storage;
use library\storage\storage\DocumentStorage;

/**
 * Class Document
 * @package library\storage
 * @property array fields
 * @property array bricks
 * @property array dynamicBricks
 * @property array content
 * @property-write \PDO dbHandle
 * @property-write DocumentStorage documentStorage
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

    public function __get($name) {
        if (in_array($name, $this->jsonEncodedFields)) {
            if (is_string($this->$name)) {
                return json_decode($this->$name);
            } else {
                return $this->$name;
            }
        } elseif ($name === 'content') {
            if ($this->dbHandle === null) {
                throw new \Exception('Document doesnt have a dbHandle handle. (path: ' . $this->path . ')');
            } else {
                if ($this->content === null) {
                    return $this->getContent();
                }
            }
        } elseif ($name === 'dbHandle') {
            throw new \Exception('Trying to get protected property repository.');
        }
        return $this->$name;
    }

    public function __set($name, $value) {
        if (in_array($name, $this->jsonEncodedFields)) {
            $this->$name = json_encode($value);
        } elseif ($name === 'content') {
            // Dont do anything for now..
            return;
        }

        $this->$name = $value;
    }

	/**
	 * @param string $orderBy
	 * @param string $order
	 *
	 * @return array
	 * @throws \Exception
	 */
    public function getContent($orderBy = 'title', $order = 'ASC')
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


}