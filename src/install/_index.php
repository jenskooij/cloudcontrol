<?php
/**
 * Created by jensk on 15-8-2017.
 */
require_once('../vendor/autoload.php');
// Error settings
ini_set('display_errors', true);
ini_set('error_reporting', E_ALL);

// Allow Short Open Tags
ini_set('short_open_tag', true);

// Set internal encoding
mb_internal_encoding("UTF-8");

// Time settings
setlocale(LC_ALL, 'nl_NL');
date_default_timezone_set('Europe/Amsterdam');

ob_start("sanitize_output");
//ob_start();
session_start();

//new \library\cc\Application();
\CloudControl\Cms\CloudControl::run();

if (php_sapi_name() != "cli") {
    ob_end_flush();
}