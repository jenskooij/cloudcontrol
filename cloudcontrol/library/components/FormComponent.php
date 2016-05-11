<?php
/**
 * Created by IntelliJ IDEA.
 * User: Jens
 * Date: 11-5-2016
 * Time: 10:02
 */

namespace library\components;


use library\cc\Request;
use library\storage\Storage;

class FormComponent Extends BaseComponent
{
    protected $documentType = null;
    protected $responseFolder = null;
    protected $subTemplate = 'cms/documents/document-form-form';
    protected $formParameterName = 'form';
    protected $thankYouMessage = 'Thank you for sending us your response.';

    private $formId;
    private $getPathBackup = null;

    public function run(Storage $storage)
    {
        parent::run($storage);

        $this->checkParameters();

        if ($this->documentType === null || $this->responseFolder === null) {
            throw new \Exception('Parameters `documentType` and `responseFolder` are required for usage with this form');
        }

        $this->setFormId();
        $this->initialize($storage);
        $this->checkSubmit($storage);
    }

    public function render($application = null)
    {
        $request = $this->request;
        if (isset($request::$get['path'])) {
            $this->getPathBackup = $request::$get['path'];
        }
        $request::$get['path'] = $this->responseFolder;
        $form = $this->renderTemplate($this->subTemplate);
        if ($this->getPathBackup !== null) {
            $request::$get['path'] = $this->getPathBackup;
        } else {
            unset($request::$get['path']);
        }
        if (!empty($request::$post) && isset($request::$post['formId']) && $request::$post['formId'] === $this->formId && isset($_SESSION['FormComponent'][$this->formParameterName]) && $_SESSION['FormComponent'][$this->formParameterName] === $this->formId) {
            $this->parameters[$this->formParameterName] = $this->thankYouMessage;
        } else {
            $this->parameters[$this->formParameterName] = $form;
        }

        parent::render($application);
    }

    private function checkParameters()
    {
        if (isset($this->parameters['documentType'])) {
            $this->documentType = $this->parameters['documentType'];
            unset($this->parameters['documentType']);
        }

        if (isset($this->parameters['responseFolder'])) {
            $this->responseFolder = $this->parameters['responseFolder'];
            unset($this->parameters['responseFolder']);
        }

        if (isset($this->parameters['subTemplate'])) {
            $this->subTemplate = $this->parameters['subTemplate'];
            unset($this->parameters['subTemplate']);
        }

        if (isset($this->parameters['formParameterName'])) {
            $this->formParameterName = $this->parameters['formParameterName'];
            unset($this->parameters['formParameterName']);
        }

        if (isset($this->parameters['thankYouMessage'])) {
            $this->thankYouMessage = $this->parameters['thankYouMessage'];
            unset($this->parameters['thankYouMessage']);
        }
    }

    private function initialize($storage)
    {
        $this->parameters['smallestImage'] = $storage->getSmallestImageSet()->slug;
        $this->parameters['cmsPrefix'] = '';

        $this->parameters['documentType'] = $this->storage->getDocumentTypeBySlug($this->documentType, true);
        $this->parameters['documentTypes'] = $this->storage->getDocumentTypes();
        $this->parameters['hideTitleAndState'] = true;
        $this->parameters['formId'] = $this->formId;
    }

    private function checkSubmit($storage)
    {
        $request = $this->request;
        if (!empty($request::$post) && isset($request::$post['formId']) && $request::$post['formId'] === $this->formId && isset($_SESSION['FormComponent'][$this->formParameterName]) && $_SESSION['FormComponent'][$this->formParameterName] === $this->formId) {
            $postValues = $request::$post;
            $postValues['documentType'] = $this->documentType;
            $postValues['path'] = $this->responseFolder;
            $postValues['title'] = date('r') . ' - From: ' . $request::$requestUri;

            $backup = null;
            if (isset($_SESSION['cloudcontrol'])) {
                $backup = $_SESSION['cloudcontrol'];
            }
            $fakeUser = new \stdClass();
            $fakeUser->username = 'FormComponent';
            $_SESSION['cloudcontrol'] = $fakeUser;

            $storage->addDocument($postValues);

            if ($backup === null) {
                unset($_SESSION['cloudcontrol']);
            } else {
                $_SESSION['cloudcontrol'] = $backup;
            }
        }
    }

    private function setFormId()
    {
        if (isset($_SESSION['FormComponent'][$this->formParameterName])) {
            $this->formId = $_SESSION['FormComponent'][$this->formParameterName];
        } else {
            $_SESSION['FormComponent'][$this->formParameterName] = (string) microtime(true);
            $this->formId = $_SESSION['FormComponent'][$this->formParameterName];
        }
    }
}