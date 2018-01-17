<?php
/**
 * Created by jensk on 15-8-2017.
 */
require_once(__DIR__ . '/../vendor/autoload.php');
if (php_sapi_name() === 'cli-server') {
    if (preg_match('/\.(?:js|ico|txt|gif|jpg|jpeg|png|bmp|css|html|htm|php|pdf|exe|eot|svg|ttf|woff|ogg|mp3|xml|map|scss)$/', $_SERVER["REQUEST_URI"])) {
        if (file_exists(__DIR__ . $_SERVER["REQUEST_URI"])) {
            return false;    // serve the requested resource as-is.
        }
    }
}
// Error settings
ini_set('display_errors', true);
ini_set('error_reporting', E_ALL);

// Allow Short Open Tags
ini_set('short_open_tag', true);

// Set internal encoding
if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding("UTF-8");
}

// Time settings
setlocale(LC_ALL, 'nl_NL');
date_default_timezone_set('Europe/Amsterdam');

ob_start("sanitize_output");
session_start();

$rootDir = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);
$configPath = realpath($rootDir . DIRECTORY_SEPARATOR . 'config.json');
\CloudControl\Cms\CloudControl::run($rootDir, $configPath);

if (php_sapi_name() != "cli") {
    ob_end_flush();
}