<?php
/**
 * User: jensk
 * Date: 13-3-2017
 * Time: 15:16
 */

namespace library\cc;

class ErrorHandlingUtil
{
	public static $JSON_ERRORS = array(
		JSON_ERROR_NONE => 'No errors',
		JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
		JSON_ERROR_STATE_MISMATCH => 'Underflow or the modes mismatch',
		JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
		JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON',
		JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded',
	);

	/**
	 * Displays the error in a human readable fashion for developers.
	 *
	 * @param string $message
	 * @param string $file
	 * @param string $line
	 * @param int    $code
	 * @param array  $trace
	 * @param string $httpHeader
	 */
	public static function renderError($message = '', $file = '', $line = '', $code = 0, $trace = array(), $httpHeader = 'HTTP/1.0 500 Internal Server Error')
	{
		if (ob_get_contents()) ob_end_clean();
		$line = intval($line);

		if (self::canShowError()) {
			self::showError($message, $file, $line, $code, $trace, $httpHeader);
		} else {
			self::dontShowError($message, $file, $line, $httpHeader);
		}
	}

	/**
	 * Error handler specificly for json errors
	 *
	 * @param $file
	 * @param $line
	 */
	public static function handleJsonError($file, $line)
	{
		$jsonErrorNr = json_last_error();
		$errstr = 'Unknown error';
		if (isset(self::$JSON_ERRORS[$jsonErrorNr])) {
			$errstr = self::$JSON_ERRORS[$jsonErrorNr];
		}
		\errorHandler($jsonErrorNr, $errstr, $file, $line);
	}

	private static function canShowError()
	{
		if (PHP_SAPI === 'cli') {
			return true;
		}
		if (file_exists('../config.json') && !isset($_SESSION['cloudcontrol'])) {
			$config = file_get_contents('../config.json');
			$config = json_decode($config);
			if (isset($config->showErrorsToAll)) {
				return $config->showErrorsToAll;
			}
		} else {
			return true;
		}
	}

	private static function renderCliException($message, $file, $line, $trace, $lines)
	{
		if (ob_get_contents()) ob_end_clean();
		include(__DIR__ . DIRECTORY_SEPARATOR . 'errortemplates/errorviewcli.php');
		exit;
	}

	/**
	 * @param $message
	 * @param $file
	 * @param $line
	 * @param $httpHeader
	 */
	private static function dontShowError($message, $file, $line, $httpHeader)
	{
		header($_SERVER['SERVER_PROTOCOL'] . $httpHeader, true);
		header('X-Error-Message: ' . $message);
		header('X-Error-File: ' . $file);
		header('X-Error-Line: ' . $line);
		if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'errortemplates/errorviewcompact.php')) {
			include(__DIR__ . DIRECTORY_SEPARATOR . 'errortemplates/errorviewcompact.php');
		} else {
			header('Content-type: application/json');
			die(json_encode('An error occured.'));
		}
	}

	/**
	 * @param $message
	 * @param $file
	 * @param $line
	 * @param $code
	 * @param $trace
	 * @param $httpHeader
	 */
	private static function showError($message, $file, $line, $code, $trace, $httpHeader)
	{
		$lines = self::getNumberedErrorLines($file, $line);

		if (PHP_SAPI === 'cli') {
			self::renderCliException($message, $file, $line, $trace, $lines);
		} else {
			self::renderGraphicException($message, $file, $line, $code, $trace, $httpHeader, $lines);
		}
	}

	/**
	 * @param $file
	 * @param $line
	 *
	 * @return array
	 */
	private static function getNumberedErrorLines($file, $line)
	{
		$file_lines = file_exists($file) ? file($file) : array();
		$range = ($line - 15) < 0 ? range(1, 30) : range($line - 15, $line + 15);
		$lines = array();

		foreach ($range as $line_number) {
			if (isset($file_lines[$line_number - 1])) {
				$lines[$line_number] = $file_lines[$line_number - 1];
			}
		}

		return $lines;
	}

	/**
	 * @param $message
	 * @param $file
	 * @param $line
	 * @param $code
	 * @param $trace
	 * @param $httpHeader
	 * @param $lines
	 */
	private static function renderGraphicException($message, $file, $line, $code, $trace, $httpHeader, $lines)
	{
		$error = array(
			'message'    => $message,
			'file'       => $file,
			'line'       => $line,
			'code'       => $code,
			'lines'      => $lines,
			'trace'      => $trace,
			'httpHeader' => $httpHeader,
		);

		if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'errortemplates/errorviewdetailed.php')) {
			header($_SERVER['SERVER_PROTOCOL'] . $httpHeader, true);
			include(__DIR__ . DIRECTORY_SEPARATOR . 'errortemplates/errorviewdetailed.php');
		} else {
			header($_SERVER['SERVER_PROTOCOL'] . $httpHeader, true);
			header('Content-type: application/json');
			die(json_encode($error));
		}
		exit;
	}
}