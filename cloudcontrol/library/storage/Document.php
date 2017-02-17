<?php
/**
 * User: Jens
 * Date: 17-2-2017
 * Time: 13:00
 */

namespace library\storage;


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

    protected $jsonEncodedFields = array('fields', 'bricks', 'dynamicBricks');

    public function __get($name) {
        if (in_array($name, $this->jsonEncodedFields)) {
            if (is_string($this->$name)) {
                return json_decode($this->$name);
            } else {
                return $this->$name;
            }
        }
        return $this->$name;
    }

    public function __set($name, $value) {
        if (in_array($name, $this->jsonEncodedFields)) {
            $this->$name = json_encode($value);
        }
        $this->$name = $value;
    }
}