<?php
/**
 * User: jensk
 * Date: 13-3-2017
 * Time: 15:04
 */

namespace library\cc;


class AutoloadUtil
{
	/**
	 * The decoupled autoload function for actually loading the classes
	 *
	 * @param      $class
	 * @param bool $throwException
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public static function autoLoad($class, $throwException = true)
	{
		if (class_exists($class, false)) {
			return true;
		}

		global $rootPath;
		$file = $rootPath . str_replace('\\', '/', $class) . ".php";

		if (file_exists($file)) {
			return self::loadClassInExistingFile($class, $throwException, $file);
		} else {
			return self::handleUnloadableClass($class, $throwException, $rootPath, $file);
		}
	}

	private static function handleUnloadableClass($class, $throwException, $rootPath, $file)
	{
		$debug_backtrace = debug_backtrace();
		if (isset($debug_backtrace[2]) && isset($debug_backtrace[2]['file']) && isset($debug_backtrace[2]['line'])) {
			return self::handleUnloadableClassWithBacktrace($class, $throwException, $rootPath, $file, $debug_backtrace);
		} else {
			return self::handleUnloadableClassWithoutBacktrace($class, $throwException, $file);
		}
	}


	/**
	 * @param $class
	 * @param $throwException
	 * @param $file
	 *
	 * @return bool
	 * @throws \Exception
	 */
	private static function loadClassInExistingFile($class, $throwException, $file)
	{
		require_once($file);
		if ($throwException) {
			if (class_exists($class, false) === false && interface_exists($class, false) === false) {
				throw new \Exception('Could not load class "' . $class . '" in file ' . $file);
			} else {
				return true;
			}
		}

		return class_exists($class, false) || interface_exists($class, false);
	}

	/**
	 * @param $class
	 * @param $throwException
	 * @param $rootPath
	 * @param $file
	 * @param $debug_backtrace
	 *
	 * @return bool
	 */
	private static function handleUnloadableClassWithBacktrace($class, $throwException, $rootPath, $file, $debug_backtrace)
	{
		if ($throwException) {
			errorHandler(0, 'Could not load class \'' . $class . '\' in file ' . $rootPath . $file, $debug_backtrace[2]['file'], $debug_backtrace[2]['line']);
		} else {
			return false;
		}
	}

	/**
	 * @param $class
	 * @param $throwException
	 * @param $file
	 *
	 * @return bool
	 * @throws \Exception
	 */
	private static function handleUnloadableClassWithoutBacktrace($class, $throwException, $file)
	{
		if ($throwException) {
			throw new \Exception('Could not load class "' . $class . '" in file ' . $file . "\n" . 'Called from unknown origin.');
		} else {
			return false;
		}
	}
}