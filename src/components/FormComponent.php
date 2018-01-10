<?php

namespace CloudControl\Cms\components;


use CloudControl\Cms\cc\Application;
use CloudControl\Cms\cc\Request;
use CloudControl\Cms\storage\Storage;

class FormComponent Extends BaseComponent
{
    const GET_PARAMETER_PATH = 'path';

    const PARAMETER_CMS_PREFIX = 'cmsPrefix';
    const PARAMETER_DOCUMENT_TYPE = 'documentType';
    const PARAMETER_DOCUMENT_TYPES = 'documentTypes';
    const PARAMETER_FORM_ID = 'formId';
    const PARAMETER_FORM_PARAMETER_NAME = 'formParameterName';
    const PARAMETER_HIDE_TITLE_AND_STATE = 'hideTitleAndState';
    const PARAMETER_RESPONSE_FOLDER = 'responseFolder';
    const PARAMETER_SMALLEST_IMAGE = 'smallestImage';
    const PARAMETER_SUBMIT_ONCE_PER_SESSION = 'submitOncePerSession';
    const PARAMETER_SUB_TEMPLATE = 'subTemplate';
    const PARAMETER_THANK_YOU_MESSAGE = 'thankYouMessage';

    const SESSION_PARAMETER_CLOUDCONTROL = 'cloudcontrol';
    const SESSION_PARAMETER_FORM_COMPONENT = 'FormComponent';
    /**
     * @var null|string
     */
    protected $documentType;
    /**
     * @var null|string
     */
    protected $responseFolder;
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
     * @var bool
     */
    protected $submitOncePerSession = false;

    /**
     * @var string
     */
    protected $formId;
    /**
     * @var null|string
     */
    protected $getPathBackup;

    /**
     * @var null|\stdClass
     */
    protected $userSessionBackup;

    /**
     * @param Storage $storage
     *
     * @return void
     * @throws \Exception
     */
    public function run(Storage $storage)
    {
        parent::run($storage);
        $this->checkParameters();
        $this->checkRequiredParameters();
        $this->setFormId();
        $this->initialize($storage);
        $this->checkSubmit($storage);
    }

    /**
     * @param null|Application $application
     *
     * @throws \Exception
     */
    public function render($application = null)
    {
        $request = $this->setPathBackup();
        $form = $this->renderTemplate($this->subTemplate);
        $this->resetPathBackup($request);
        $this->setFormParameter($form);

        parent::render($application);
    }

    /**
     * Checks if parameters were given in the CMS configuration and
     * sets them to their respective fields
     */
    protected function checkParameters()
    {
        $this->checkDocumentTypeParameter();
        $this->checkResponseFolderParameter();
        $this->checkSubTemplateParameter();
        $this->checkFormParameterNameParameter();
        $this->checkThankYouMessageParameter();
        $this->checkSubmitOncePerSessionParameter();
    }

    /**
     * Sets variables needed for rendering the form template
     *
     * @param Storage $storage
     */
    protected function initialize($storage)
    {
        $this->parameters[self::PARAMETER_SMALLEST_IMAGE] = $storage->getImageSet()->getSmallestImageSet();
        $this->parameters[self::PARAMETER_CMS_PREFIX] = '';

        $this->parameters[self::PARAMETER_DOCUMENT_TYPE] = $this->storage->getDocumentTypes()->getDocumentTypeBySlug($this->documentType, true);
        $this->parameters[self::PARAMETER_DOCUMENT_TYPES] = $this->storage->getDocumentTypes()->getDocumentTypes();
        $this->parameters[self::PARAMETER_HIDE_TITLE_AND_STATE] = true;
        $this->parameters[self::PARAMETER_FORM_ID] = $this->formId;
    }

    /**
     * If the form has been submitted, save the document
     * Calls $this->postSubmit() afterwards
     *
     * @param Storage $storage
     */
    protected function checkSubmit($storage)
    {
        if ($this->isFormSubmitted($this->request) && $this->isSubmitAllowed()) {
            $postValues = $this->getPostValues($this->request);
            $this->setUserSessionBackup();
            $storage->getDocuments()->addDocument($postValues);
            $this->restoreUserSessionBackup();
            $this->setSubmitToSession();
            $this->postSubmit($postValues, $storage);
        }
    }

    /**
     * Hook for derived classes to take actions after
     * submitting the form
     *
     * @param $postValues
     * @param Storage $storage
     */
    protected function postSubmit(/** @scrutinizer ignore-unused */$postValues, /** @scrutinizer ignore-unused */$storage)
    {
    }

    /**
     * Sets a unique id for this particular form, so it can recognize
     * it when a submit occurs
     */
    protected function setFormId()
    {
        if (isset($_SESSION[self::SESSION_PARAMETER_FORM_COMPONENT][$this->formParameterName][self::PARAMETER_FORM_ID])) {
            $this->formId = $_SESSION[self::SESSION_PARAMETER_FORM_COMPONENT][$this->formParameterName][self::PARAMETER_FORM_ID];
        } else {
            $_SESSION[self::SESSION_PARAMETER_FORM_COMPONENT][$this->formParameterName][self::PARAMETER_FORM_ID] = (string)microtime(true);
            $_SESSION[self::SESSION_PARAMETER_FORM_COMPONENT][$this->formParameterName]['submitted'] = false;
            $this->formId = $_SESSION[self::SESSION_PARAMETER_FORM_COMPONENT][$this->formParameterName][self::PARAMETER_FORM_ID];
        }
    }

    /**
     * Checks if this form has been submitted
     *
     * @param \CloudControl\Cms\cc\Request $request
     *
     * @return bool
     */
    protected function isFormSubmitted($request)
    {
        return !empty($request::$post) && isset($request::$post[self::PARAMETER_FORM_ID]) && $request::$post[self::PARAMETER_FORM_ID] === $this->formId && isset($_SESSION[self::SESSION_PARAMETER_FORM_COMPONENT][$this->formParameterName][self::PARAMETER_FORM_ID]) && $_SESSION[self::SESSION_PARAMETER_FORM_COMPONENT][$this->formParameterName][self::PARAMETER_FORM_ID] === $this->formId;
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function getPostValues($request)
    {
        $postValues = $request::$post;
        $postValues[self::PARAMETER_DOCUMENT_TYPE] = $this->documentType;
        $postValues[self::GET_PARAMETER_PATH] = $this->responseFolder;
        $postValues['title'] = date('r') . ' - From: ' . $request::$requestUri;

        return $postValues;
    }

    /**
     * Temporarily stores the current user session in a backup variable
     * and sets a fake user instead
     */
    protected function setUserSessionBackup()
    {
        $this->userSessionBackup = isset($_SESSION[self::SESSION_PARAMETER_CLOUDCONTROL]) ? $_SESSION[self::SESSION_PARAMETER_CLOUDCONTROL] : null;
        $fakeUser = new \stdClass();
        $fakeUser->username = self::SESSION_PARAMETER_FORM_COMPONENT;
        $_SESSION[self::SESSION_PARAMETER_CLOUDCONTROL] = $fakeUser;
    }

    /**
     * Removes the fake user and restores the existing user
     * session if it was there
     */
    protected function restoreUserSessionBackup()
    {
        if ($this->userSessionBackup === null) {
            unset($_SESSION[self::SESSION_PARAMETER_CLOUDCONTROL]);
        } else {
            $_SESSION[self::SESSION_PARAMETER_CLOUDCONTROL] = $this->userSessionBackup;
        }
    }

    protected function setSubmitToSession()
    {
        $_SESSION[self::SESSION_PARAMETER_FORM_COMPONENT][$this->formParameterName]['submitted'] = true;
    }

    protected function isSubmitAllowed()
    {
        if ($this->submitOncePerSession === true && $_SESSION[self::SESSION_PARAMETER_FORM_COMPONENT][$this->formParameterName]['submitted'] === true) {
            return false;
        } else {
            return true;
        }
    }

    protected function checkDocumentTypeParameter()
    {
        if (isset($this->parameters[self::PARAMETER_DOCUMENT_TYPE])) {
            $this->documentType = $this->parameters[self::PARAMETER_DOCUMENT_TYPE];
            unset($this->parameters[self::PARAMETER_DOCUMENT_TYPE]);
        }
    }

    protected function checkResponseFolderParameter()
    {
        if (isset($this->parameters[self::PARAMETER_RESPONSE_FOLDER])) {
            $this->responseFolder = $this->parameters[self::PARAMETER_RESPONSE_FOLDER];
            unset($this->parameters[self::PARAMETER_RESPONSE_FOLDER]);
        }
    }

    protected function checkSubTemplateParameter()
    {
        if (isset($this->parameters[self::PARAMETER_SUB_TEMPLATE])) {
            $this->subTemplate = $this->parameters[self::PARAMETER_SUB_TEMPLATE];
            unset($this->parameters[self::PARAMETER_SUB_TEMPLATE]);
        }
    }

    protected function checkFormParameterNameParameter()
    {
        if (isset($this->parameters[self::PARAMETER_FORM_PARAMETER_NAME])) {
            $this->formParameterName = $this->parameters[self::PARAMETER_FORM_PARAMETER_NAME];
            unset($this->parameters[self::PARAMETER_FORM_PARAMETER_NAME]);
        }
    }

    protected function checkThankYouMessageParameter()
    {
        if (isset($this->parameters[self::PARAMETER_THANK_YOU_MESSAGE])) {
            $this->thankYouMessage = $this->parameters[self::PARAMETER_THANK_YOU_MESSAGE];
            unset($this->parameters[self::PARAMETER_THANK_YOU_MESSAGE]);
        }
    }

    protected function checkSubmitOncePerSessionParameter()
    {
        if (isset($this->parameters[self::PARAMETER_SUBMIT_ONCE_PER_SESSION])) {
            $this->submitOncePerSession = $this->parameters[self::PARAMETER_SUBMIT_ONCE_PER_SESSION] === 'true';
            unset($this->parameters[self::PARAMETER_SUBMIT_ONCE_PER_SESSION]);
        }
    }

    /**
     * @throws \Exception
     */
    protected function checkRequiredParameters()
    {
        if ($this->documentType === null || $this->responseFolder === null) {
            throw new \Exception('Parameters `documentType` and `responseFolder` are required for usage with this form');
        }
    }

    /**
     * @return Request
     */
    protected function setPathBackup()
    {
        $request = $this->request;
        if (isset($request::$get[self::GET_PARAMETER_PATH])) {
            $this->getPathBackup = $request::$get[self::GET_PARAMETER_PATH];
        }
        $request::$get[self::GET_PARAMETER_PATH] = $this->responseFolder;

        return $request;
    }

    /**
     * @param Request $request
     */
    protected function resetPathBackup($request)
    {
        if ($this->getPathBackup !== null) {
            $request::$get[self::GET_PARAMETER_PATH] = $this->getPathBackup;
        } else {
            $get = $request::$get;
            unset($get[self::GET_PARAMETER_PATH]);
            $request::$get = $get;
        }
    }

    /**
     * @param string|null $form
     */
    protected function setFormParameter($form)
    {
        if ($this->isFormSubmitted($this->request) || $this->isSubmitAllowed() === false) {
            $this->parameters[$this->formParameterName] = '<a name="' . $this->formId . '"></a>' . $this->thankYouMessage;
        } else {
            $this->parameters[$this->formParameterName] = $form;
        }
    }
}