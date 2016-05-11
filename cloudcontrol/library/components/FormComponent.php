<?php
namespace library\components;


use library\cc\Application;
use library\storage\Storage;

class FormComponent Extends BaseComponent
{
    /**
     * @var null|string
     */
    protected $documentType = null;
    /**
     * @var null|string
     */
    protected $responseFolder = null;
    /**
     * @var string
     */
    protected $subTemplate = 'cms/documents/document-form-form';
    /**
     * @var string
     */
    protected $formParameterName = 'form';
    /**
     * @var string
     */
    protected $thankYouMessage = 'Thank you for sending us your response.';

    /**
     * @var string
     */
    private $formId;
    /**
     * @var null|string
     */
    private $getPathBackup = null;

    /**
     * @var null|\stdClass
     */
    private $userSessionBackup = null;

    /**
     * @param Storage $storage
     * @return void
     * @throws \Exception
     */
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

    /**
     * @param null|Application $application
     * @throws \Exception
     */
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
        if ($this->isFormSubmitted($this->request)) {
            $this->parameters[$this->formParameterName] = '<a name="' . $this->formId . '"></a>' . $this->thankYouMessage;
        } else {
            $this->parameters[$this->formParameterName] = $form;
        }

        parent::render($application);
    }

    /**
     * Checks if parameters were given in the CMS configuration and
     * sets them to their respective fields
     */
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

    /**
     * Sets variables needed for rendering the form template
     * @param $storage
     */
    private function initialize($storage)
    {
        $this->parameters['smallestImage'] = $storage->getSmallestImageSet()->slug;
        $this->parameters['cmsPrefix'] = '';

        $this->parameters['documentType'] = $this->storage->getDocumentTypeBySlug($this->documentType, true);
        $this->parameters['documentTypes'] = $this->storage->getDocumentTypes();
        $this->parameters['hideTitleAndState'] = true;
        $this->parameters['formId'] = $this->formId;
    }

    /**
     * If the form has been submitted, save the document
     * Calls $this->postSubmit() afterwards
     *
     * @param Storage $storage
     */
    private function checkSubmit($storage)
    {
        if ($this->isFormSubmitted($this->request)) {
            $postValues = $this->getPostValues($this->request);
            $this->setUserSessionBackup();
            $storage->addDocument($postValues);
            $this->restoreUserSessionBackup();
            $this->postSubmit($postValues, $storage)
        }
    }

    /**
     * Hook for derived classes to take actions after
     * submitting the form
     *
     * @param $postValues
     * @param $storage
     */
    protected function postSubmit($postValues, $storage)
    {}

    /**
     * Sets a unique id for this particular form, so it can recognize
     * it when a submit occurs
     */
    private function setFormId()
    {
        if (isset($_SESSION['FormComponent'][$this->formParameterName])) {
            $this->formId = $_SESSION['FormComponent'][$this->formParameterName];
        } else {
            $_SESSION['FormComponent'][$this->formParameterName] = (string) microtime(true);
            $this->formId = $_SESSION['FormComponent'][$this->formParameterName];
        }
    }

    /**
     * Checks if this form has been submitted
     *
     * @param $request
     * @return bool
     */
    private function isFormSubmitted($request)
    {
        return !empty($request::$post) && isset($request::$post['formId']) && $request::$post['formId'] === $this->formId && isset($_SESSION['FormComponent'][$this->formParameterName]) && $_SESSION['FormComponent'][$this->formParameterName] === $this->formId;
    }

    /**
     *
     *
     * @param $request
     */
    private function getPostValues($request)
    {
        $postValues = $request::$post;
        $postValues['documentType'] = $this->documentType;
        $postValues['path'] = $this->responseFolder;
        $postValues['title'] = date('r') . ' - From: ' . $request::$requestUri;
    }

    /**
     * Temporarily stores the current user session in a backup variable
     * and sets a fake user instead
     */
    private function setUserSessionBackup()
    {
        $this->userSessionBackup = isset($_SESSION['cloudcontrol']) ? $_SESSION['cloudcontrol'] : null;
        $fakeUser = new \stdClass();
        $fakeUser->username = 'FormComponent';
        $_SESSION['cloudcontrol'] = $fakeUser;
    }

    /**
     * Removes the fake user and restores the existing user
     * session if it was there
     */
    private function restoreUserSessionBackup()
    {
        if ($this->userSessionBackup === null) {
            unset($_SESSION['cloudcontrol']);
        } else {
            $_SESSION['cloudcontrol'] = $this->userSessionBackup;
        }
    }
}