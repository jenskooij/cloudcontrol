<?php
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

ob_start();
session_start();

include('errorhandler.php');
include('autoloader.php');

new \library\cc\Application();

if (php_sapi_name() != "cli") {
	ob_end_flush();
}

/** @noinspection PhpDocSignatureInspection */

/**
 * Dumps a var_dump of the passed arguments with <pre> tags surrounding it.
 * Dies afterwards
 *
 * @param mixed ...    The data to be displayed
 */
function dump()
{
	$debug_backtrace = current(debug_backtrace());
	if (PHP_SAPI == 'cli') {
		echo 'Dump: ' . $debug_backtrace['file'] . ':' . $debug_backtrace['line'] . "\n";
		foreach (func_get_args() as $data) {
			var_dump($data);
		}
	} else {
		ob_clean();
		echo '<div>Dump: ' . $debug_backtrace['file'] . ':<b>' . $debug_backtrace['line'] . "</b></div>";
		echo '<pre>';
		foreach (func_get_args() as $data) {
			echo "<code>";
			var_dump($data);
			echo "</code>";
		}
		echo '</pre>';
		echo <<<END
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.0.0/highlight.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.0.0/styles/default.min.css" />
<script>hljs.initHighlightingOnLoad();</script>
<style>code.php.hljs{margin: -0.4em 0;}code.active{background-color:#e0e0e0;}code:before{content:attr(data-line);}code.active:before{color:#c00;font-weight:bold;}</style>
END;
	}
	die;
}

/**
 * Initializes the framework by creating the default
 * storage and base template
 */
function initFramework()
{
	$baseTemplateDefaultPath = realpath('../library/cc/install/_base.php');
	$baseTemplateTargetPath = '../templates/base.php';
	$baseConfigDefaultPath = realpath('../library/cc/install/_config.json');
	$baseConfigTargetPath = '../config.json';

	// Create the initial config
	if (file_exists($baseConfigDefaultPath) && realpath($baseConfigTargetPath) === false) {
		copy($baseConfigDefaultPath, $baseConfigTargetPath);
	}

	// Create the initial base template
	if (file_exists($baseTemplateDefaultPath) && realpath($baseTemplateTargetPath) === false) {
		copy($baseTemplateDefaultPath, $baseTemplateTargetPath);
	}
}

/**
 * Convert all values of an array to utf8
 *
 * @param $array
 * @return array
 */
function utf8Convert($array)
{
	array_walk_recursive($array, function(&$item){
		if(!mb_detect_encoding($item, 'utf-8', true)){
			$item = utf8_encode($item);
		}
	});

	return $array;
}