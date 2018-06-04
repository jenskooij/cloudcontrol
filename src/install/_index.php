<?php
/**
 * Cloud Control Bootstrap / Router script
 * Editing this is not recommended, as it gets overwritten
 * everytime composer update / install is ran.
 */
require_once(__DIR__ . '/../vendor/autoload.php');

use CloudControl\Cms\cc\Request;
use CloudControl\Cms\CloudControl;


if (CloudControl::cliServerServeResource(__DIR__)) {
    return false;
}

if (PHP_SAPI === 'cli') {
    Request::$argv = $argv;
}

$preparation = CloudControl::prepare(__DIR__);
/** @noinspection PhpUnhandledExceptionInspection */
CloudControl::run($preparation->rootDir, $preparation->configPath);

if (PHP_SAPI !== 'cli') {
    ob_end_flush();
}