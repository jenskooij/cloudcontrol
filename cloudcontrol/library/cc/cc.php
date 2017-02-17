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

/**
 * @param $ptime
 * @return string|void
 */
function timeElapsedString($ptime)
{
	$etime = time() - $ptime;

	if ($etime < 1)
	{
		return '0 seconds';
	}

	$a = array( 365 * 24 * 60 * 60  =>  'year',
				30 * 24 * 60 * 60  =>  'month',
				24 * 60 * 60  =>  'day',
				60 * 60  =>  'hour',
				60  =>  'minute',
				1  =>  'second'
	);
	$a_plural = array( 'year'   => 'years',
					   'month'  => 'months',
					   'day'    => 'days',
					   'hour'   => 'hours',
					   'minute' => 'minutes',
					   'second' => 'seconds'
	);

	foreach ($a as $secs => $str)
	{
		$d = $etime / $secs;
		if ($d >= 1)
		{
			$r = round($d);
			return $r . ' ' . ($r > 1 ? $a_plural[$str] : $str) . ' ago';
		}
	}
	return 0;
}

/**
 * Converts an amount of bytes to a human readable
 * format
 *
 * @param $size
 * @param string $unit
 * @return string
 */
function humanFileSize($size,$unit="") {
	if( (!$unit && $size >= 1<<30) || $unit == "GB")
		return number_format($size/(1<<30),2)."GB";
	if( (!$unit && $size >= 1<<20) || $unit == "MB")
		return number_format($size/(1<<20),2)."MB";
	if( (!$unit && $size >= 1<<10) || $unit == "KB")
		return number_format($size/(1<<10),2)."KB";
	return number_format($size)." bytes";
}

/**
 * Selects the right font-awesome icon for each filetype
 *
 * @param $fileType
 * @return string
 */

function iconByFileType($fileType) {
	if (strpos($fileType, 'image') !== false) {
		return 'file-image-o';
	} elseif (strpos($fileType, 'pdf') !== false) {
		return 'file-pdf-o';
	} elseif (strpos($fileType, 'audio') !== false) {
		return 'file-audio-o';
	} elseif (strpos($fileType, 'text') !== false) {
		return 'file-text-o';
	} elseif (strpos($fileType, 'x-msdownload') !== false) {
		return 'windows';
	} elseif (in_array($fileType, array(
		'application/vnd.ms-excel',
		'application/msexcel',
		'application/xls',
		'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		'application/vnd.google-apps.spreadsheet',
	))) {
		return 'file-excel-o';
	} elseif (in_array($fileType, array(
		'application/msword',
		'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
	))) {
		return 'file-word-o';
	} elseif (in_array($fileType, array(
		'application/x-rar-compressed',
		'application/x-zip-compressed',
		'application/zip',
	))) {
		return 'file-archive-o';
	}
	return 'file-o';
}

/**
 * Convert a string to url friendly slug
 *
 * @param string $str
 * @param array  $replace
 * @param string $delimiter
 *
 * @return mixed|string
 */
function slugify($str, $replace=array(), $delimiter='-') {
	if( !empty($replace) ) {
		$str = str_replace((array)$replace, ' ', $str);
	}

	$clean = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);
	$clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
	$clean = strtolower(trim($clean, '-'));
	$clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);

	return $clean;
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
 *
 * @param string $storagePath
 */
function initFramework($storagePath)
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
	array_walk_recursive($array, function(&$item, $key){
		if(!mb_detect_encoding($item, 'utf-8', true)){
			$item = utf8_encode($item);
		}
	});

	return $array;
}