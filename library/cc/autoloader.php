<?php
spl_autoload_extensions('.php');
spl_autoload_register("autoloader");

$rootPath = str_replace('\\', '/', realpath(str_replace('\\', '/', dirname(__FILE__)) . '/../../') . '/');

function autoloader($class) {
	autoLoad($class);	
}

function autoLoad($class, $throwException = true)
{
	if (class_exists($class, false)) {
		return true;
	}
	
	global $rootPath;
	$file = $rootPath . str_replace('\\', '/', $class) . ".php";
	$debug_backtrace = debug_backtrace();
	
	if (file_exists($file)) {
		require_once($file);
		if ($throwException) {
			if (class_exists($class, false) == false && interface_exists($class, false) == false) {
				throw new \Exception('Could not load class "' . $class . '" in file ' . $file);
			} else {
				return true;
			}
		}
		return class_exists($class, false) || interface_exists($class, false);
	} else {
		if (isset($debug_backtrace[2]) && isset($debug_backtrace[2]['file']) && isset($debug_backtrace[2]['line'])) {			
			if ($throwException) {
				errorHandler(0, 'Could not load class \'' . $class . '\' in file ' . $rootPath . $file, $debug_backtrace[2]['file'], $debug_backtrace[2]['line']);
			} else {
				return false;
			}			
		} else {
			if ($throwException) {
				throw new \Exception('Could not load class "' . $class . '" in file ' . $file . "\n" . 'Called from unknown origin.');
			} else {
				return false;
			}
		}
	}
	return false;
}
?>