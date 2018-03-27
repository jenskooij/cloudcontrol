<?php
/**
 * Created by jensk on 15-8-2017.
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