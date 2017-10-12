<?php

namespace CloudControl\Cms\components;


use CloudControl\Cms\cc\Request;
use CloudControl\Cms\storage\Storage;

class LanguageComponent implements Component
{
    const SESSION_PARAMETER_LANGUAGE = 'language';
    const SESSION_PARAMETER_DETECTED_LANGUAGE = 'detectedLanguage';
    const SESSION_PARAMETER_LANGUAGE_COMPONENT = 'LanguageComponent';

    const HTTP_ACCEPT_LANGUAGE = 'HTTP_ACCEPT_LANGUAGE';

    const PARAMETER_DEFAULT_LANGUAGE = 'defaultLanguage';
    const PARAMETER_ACCEPTED_LANGUAGES = 'acceptedLanguages';
    const PARAMETER_LANGUAGE_PARAMETER_NAME = 'languageParameterName';
    const PARAMETER_FORCE_REDIRECT = 'forceRedirect';

    protected $request;
    protected $parameters;

    protected $defaultLanguage = 'en';
    protected $acceptedLanguages = null;
    protected $languageParameterName = self::SESSION_PARAMETER_LANGUAGE;
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

        $lang = substr(isset($_SERVER[self::HTTP_ACCEPT_LANGUAGE]) ? $_SERVER[self::HTTP_ACCEPT_LANGUAGE] : $this->defaultLanguage, 0, 2);
        $_SESSION[self::SESSION_PARAMETER_LANGUAGE_COMPONENT][self::SESSION_PARAMETER_DETECTED_LANGUAGE] = $lang;

        $this->checkLanguageSwitch($request);

        if (!isset($_SESSION[self::SESSION_PARAMETER_LANGUAGE_COMPONENT][$this->languageParameterName])) {
            $this->detectLanguage($lang, $request);
        } else {
            if ($this->forceRedirect === true) {
                $this->detectLanguage($_SESSION[self::SESSION_PARAMETER_LANGUAGE_COMPONENT][self::SESSION_PARAMETER_LANGUAGE], $request);
            }
        }

        $this->parameters[$this->languageParameterName] = $_SESSION[self::SESSION_PARAMETER_LANGUAGE_COMPONENT][self::SESSION_PARAMETER_LANGUAGE];
    }

    /**
     * Checks to see if any parameters are given from the configuration in the CMS
     */
    protected function checkParameters()
    {
        if (isset($this->parameters[self::PARAMETER_DEFAULT_LANGUAGE])) {
            $this->defaultLanguage = $this->parameters[self::PARAMETER_DEFAULT_LANGUAGE];
            unset($this->parameters[self::PARAMETER_DEFAULT_LANGUAGE]);
        }
        if (isset($this->parameters[self::PARAMETER_ACCEPTED_LANGUAGES])) {
            $this->acceptedLanguages = explode(',', $this->parameters[self::PARAMETER_ACCEPTED_LANGUAGES]);
            unset($this->parameters[self::PARAMETER_ACCEPTED_LANGUAGES]);
        }
        if (isset($this->parameters[self::PARAMETER_LANGUAGE_PARAMETER_NAME])) {
            $this->languageParameterName = $this->parameters[self::PARAMETER_LANGUAGE_PARAMETER_NAME];
            unset($this->parameters[self::PARAMETER_LANGUAGE_PARAMETER_NAME]);
        }
        if (isset($this->parameters[self::PARAMETER_FORCE_REDIRECT])) {
            $this->forceRedirect = (bool)$this->parameters[self::PARAMETER_FORCE_REDIRECT];
            unset($this->parameters[self::PARAMETER_FORCE_REDIRECT]);
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

        $this->sessionValues = $_SESSION[self::SESSION_PARAMETER_LANGUAGE_COMPONENT];

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
        $_SESSION[self::SESSION_PARAMETER_LANGUAGE_COMPONENT][self::SESSION_PARAMETER_LANGUAGE] = $this->defaultLanguage;

        if ($this->acceptedLanguages === null) {
            $_SESSION[self::SESSION_PARAMETER_LANGUAGE_COMPONENT][self::SESSION_PARAMETER_LANGUAGE] = $lang;
        } else if (in_array($lang, $this->acceptedLanguages)) {
            $_SESSION[self::SESSION_PARAMETER_LANGUAGE_COMPONENT][self::SESSION_PARAMETER_LANGUAGE] = $lang;
        } else {
            $lang = $this->defaultLanguage;
        }
        return $lang;
    }
}