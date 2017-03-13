<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'AutoloadUtil.php');
spl_autoload_extensions('.php');
spl_autoload_register("autoloader");

$rootPath = str_replace('\\', '/', realpath(str_replace('\\', '/', dirname(__FILE__)) . '/../../') . '/');

/**
 * The function to be registered as the default autoload function
 * for loading classes
 *
 * @param $class
 *
 * @throws \Exception
 */
function autoloader($class) {
	\library\cc\AutoloadUtil::autoLoad($class);
}