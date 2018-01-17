<?php
/**
 * Created by jensk on 12-10-2017.
 */

namespace CloudControl\Cms\services;


use CloudControl\Cms\cc\Request;
use CloudControl\Cms\components\LanguageComponent;

class LinkService
{
    private static $instance;

    /**
     * LinkService constructor.
     */
    protected function __construct()
    {
    }

    /**
     * @return LinkService
     */
    public static function getInstance()
    {
        if (!self::$instance instanceof LinkService) {
            self::$instance = new LinkService();
        }
        return self::$instance;
    }

    /**
     * Get the (language aware) link to a relative path
     *
     * @param $relativePath
     * @return string
     */
    public static function get($relativePath)
    {
        $relativePath = substr($relativePath, 0, 1) === '/' ? substr($relativePath, 1) : $relativePath;
        if (isset($_SESSION[LanguageComponent::SESSION_PARAMETER_LANGUAGE_COMPONENT][LanguageComponent::SESSION_PARAMETER_LANGUAGE])) {
            $language = $_SESSION[LanguageComponent::SESSION_PARAMETER_LANGUAGE_COMPONENT][LanguageComponent::SESSION_PARAMETER_LANGUAGE];
            if ($language == LanguageComponent::$DEFAULT_LANGUAGE) {
                return Request::$subfolders . $relativePath;
            } else {
                return Request::$subfolders . $language . '/' . $relativePath;
            }
        } else {
            return Request::$subfolders . $relativePath;
        }
    }
}