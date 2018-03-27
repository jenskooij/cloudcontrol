<?php
namespace CloudControl\Cms\components\api;

/**
 * Class Response
 * @package ApiComponent
 * @property string $folder The path of the folder that is found
 * @property array $searchSuggestions Search suggestions
 */
class Response
{
    public $success = true;
    public $results = array();
    public $error;

    public function __construct($results = array(), $success = true, $error = null)
    {
        $this->results = $results;
        $this->error = $error;
        $this->success = $success;
    }


    public function __toString()
    {
        if (!is_array($this->results)) {
            $this->results = array($this->results);
        }
        return json_encode($this);
    }
}