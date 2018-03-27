<?php
/**
 * Created by jensk on 14-8-2017.
 */

namespace CloudControl\Cms;

use CloudControl\Cms\cc\Application;
use CloudControl\Cms\storage\Cache;
use CloudControl\Cms\storage\Repository;
use Composer\Script\Event;

class CloudControl
{
    /**
     * @param $dir
     * @return \stdClass
     */
    public static function prepare($dir)
    {
        self::iniSets();
        self::setInternalEncoding();
        self::setLocalisation();


        ob_start('sanitize_output');
        session_start();

        $rootDir = realpath($dir . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);
        $configPath = realpath($rootDir . DIRECTORY_SEPARATOR . 'config.json');
        $preparation = new \stdClass();
        $preparation->rootDir = $rootDir;
        $preparation->configPath = $configPath;
        return $preparation;
    }

    /**
     * @param string $rootDir
     * @param string $configPath
     * @throws \Exception
     */
    public static function run($rootDir, $configPath)
    {
        new Application($rootDir, $configPath);
    }

    public static function cliServerServeResource($dir)
    {
        if (PHP_SAPI === 'cli-server') {
            if (preg_match('/\.(?:js|ico|txt|gif|jpg|jpeg|png|bmp|css|html|htm|php|pdf|exe|eot|svg|ttf|woff|ogg|mp3|xml|map|scss)$/', $_SERVER['REQUEST_URI'])) {
                if (file_exists($dir . $_SERVER["REQUEST_URI"])) {
                    return true;    // serve the requested resource as-is.
                }
            }
        }
        return false;
    }

    /**
     * ini settings
     */
    private static function iniSets()
    {
        // Error settings
        ini_set('display_errors', true);
        ini_set('error_reporting', E_ALL);

        // Allow Short Open Tags
        ini_set('short_open_tag', true);
    }

    /**
     * Set internal encoding
     */
    private static function setInternalEncoding()
    {
        if (function_exists('mb_internal_encoding')) {
            mb_internal_encoding('UTF-8');
        }
    }

    /**
     * Time settings
     */
    private static function setLocalisation()
    {
        setlocale(LC_ALL, 'nl_NL');
        date_default_timezone_set('Europe/Amsterdam');
    }

}