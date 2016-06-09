<?php
namespace library\components;


use library\cc\Request;
use library\storage\Storage;

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
        $this->parameters = (array) $parameters;
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
    private function checkParameters()
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
            $this->forceRedirect = (bool) $this->parameters['forceRedirect'];
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
     * @param $request
     */
    private function detectLanguage($lang, $request)
    {
        $_SESSION['LanguageComponent'][$this->languageParameterName] = $this->defaultLanguage;

        if ($this->acceptedLanguages === null) {
            $_SESSION['LanguageComponent'][$this->languageParameterName] = $lang;
        } else if (in_array($lang, $this->acceptedLanguages)) {
            $_SESSION['LanguageComponent'][$this->languageParameterName] = $lang;
        } else {
            $lang = $this->defaultLanguage;
        }

        $this->sessionValues = $_SESSION['LanguageComponent'];

        if ($this->forceRedirect === true) {
            if (substr($request::$relativeUri, 0, 2) !== $lang ) {
                if ($lang !== $this->defaultLanguage) {
                    header('Location: ' . $request::$subfolders . $lang . '/' . $request::$relativeUri);
                    exit;
                }
            }
        }
    }

    /**
     * Detect if the language is switched manually
     *
     * @param $request
     */
    private function checkLanguageSwitch($request)
    {
        if (isset($request::$get['langSwitch'])) {
            $this->forceRedirect = true;
            $this->detectLanguage($request::$get['langSwitch'], $request);
        }
    }

    /*
     * These functions are required by the interface, but not for the functionality
     */
    public function run(Storage $storage) {}
    public function render() {}
    public function get() {}
}