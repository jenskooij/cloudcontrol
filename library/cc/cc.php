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
}

/**
 * Dumps a var_dump of the passed arguments with <pre> tags surrounding it.
 * Dies afterwards
 *
 * @param mixed $data    The data to be displayed
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
?>