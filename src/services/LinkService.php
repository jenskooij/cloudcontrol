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
    {}

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

    public static function get($relativePath)
    {
        if (isset($_SESSION[LanguageComponent::SESSION_PARAMETER_LANGUAGE_COMPONENT][LanguageComponent::SESSION_PARAMETER_LANGUAGE])) {
            dump('language logic');
        } else {
            return Request::$subfolders . $relativePath;
        }
    }
}