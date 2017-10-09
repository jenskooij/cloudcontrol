<?php

namespace CloudControl\Cms\components;


use CloudControl\Cms\cc\Request;
use CloudControl\Cms\storage\Storage;

class LanguageComponent implements Component
{
    protected $request;
    protected $parameters;

    protected $defaultLanguage = 'en';
    protected $acceptedLanguages = null;
    protected $languageParameterName = 'language';
    protected $forceRedirect = false;
    protected $sessionValues;

    /**
     * Component constructor.
     *
     * @param                     $template
     * @param Request $request
     * @param                     $parameters
     * @param                     $matchedSitemapItem
     */
    public function __construct($template, Request $request, $parameters, $matchedSitemapItem)
    {
        $this->parameters = (array)$parameters;
        $this->checkParameters();

        $lang = substr(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : $this->defaultLanguage, 0, 2);
        $_SESSION['LanguageComponent']['detectedLanguage'] = $lang;

        $this->checkLanguageSwitch($request);

        if (!isset($_SESSION['LanguageComponent'][$this->languageParameterName])) {
            $this->detectLanguage($lang, $request);
        } else {
            if ($this->forceRedirect === true) {
                $this->detectLanguage($_SESSION['LanguageComponent'][$this->languageParameterName], $request);
            }
        }

        $this->parameters[$this->languageParameterName] = $_SESSION['LanguageComponent'][$this->languageParameterName];
    }

    /**
     * Checks to see if any parameters are given from the configuration in the CMS
     */
    protected function checkParameters()
    {
        if (isset($this->parameters['defaultLanguage'])) {
            $this->defaultLanguage = $this->parameters['defaultLanguage'];
            unset($this->parameters['defaultLanguage']);
        }
        if (isset($this->parameters['acceptedLanguages'])) {
            $this->acceptedLanguages = explode(',', $this->parameters['acceptedLanguages']);
            unset($this->parameters['acceptedLanguages']);
        }
        if (isset($this->parameters['languageParameterName'])) {
            $this->languageParameterName = $this->parameters['languageParameterName'];
            unset($this->parameters['languageParameterName']);
        }
        if (isset($this->parameters['forceRedirect'])) {
            $this->forceRedirect = (bool)$this->parameters['forceRedirect'];
            unset($this->parameters['forceRedirect']);
        }
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }


    /**
     * Check if the found language is allowed and
     * if an action is to be taken.
     *
     * @param $lang
     * @param Request $request
     */
    protected function detectLanguage($lang, $request)
    {
        $lang = $this->setLanguagInSession($lang);

        $this->sessionValues = $_SESSION['LanguageComponent'];

        $this->checkForceRedirect($lang, $request);
    }

    /**
     * Detect if the language is switched manually
     *
     * @param Request $request
     */
    protected function checkLanguageSwitch($request)
    {
        if (isset($request::$get['langSwitch'])) {
            $this->forceRedirect = true;
            $this->detectLanguage($request::$get['langSwitch'], $request);
        }
    }

    public function render()
    {
    }

    public function get()
    {
    }

    /**
     * @param Storage $storage
     */
    function run(Storage $storage)
    {
        // To be implemented
    }

    /**
     * @param $lang
     * @param $request
     */
    protected function checkForceRedirect($lang, $request)
    {
        if ($this->forceRedirect === true) {
            if (substr($request::$relativeUri, 0, 2) !== $lang) {
                if ($lang !== $this->defaultLanguage) {
                    $redirectUrl = $request::$subfolders . $lang . '/' . $request::$relativeUri;
                    if (!empty($request::$queryString)) {
                        $redirectUrl .= '?' . $request::$queryString;
                    }
                    header('Location: ' . $redirectUrl);
                    exit;
                }
            }
        }
    }

    /**
     * @param $lang
     * @return string
     */
    protected function setLanguagInSession($lang)
    {
        $_SESSION['LanguageComponent'][$this->languageParameterName] = $this->defaultLanguage;

        if ($this->acceptedLanguages === null) {
            $_SESSION['LanguageComponent'][$this->languageParameterName] = $lang;
        } else if (in_array($lang, $this->acceptedLanguages)) {
            $_SESSION['LanguageComponent'][$this->languageParameterName] = $lang;
        } else {
            $lang = $this->defaultLanguage;
        }
        return $lang;
    }
}