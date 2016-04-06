<?php
set_exception_handler('exceptionHandler');
set_error_handler('errorHandler');
register_shutdown_function('shutdownHandler');

function exceptionHandler ($e) {
	renderError($e->getMessage(),$e->getFile(),$e->getLine(),$e->getCode(),$e->getTrace());
}

function errorHandler ($errno, $errstr, $errfile, $errline) {
	renderError($errstr,$errfile,$errline,$errno,debug_backtrace());
}

function shutdownHandler () {
	$error = error_get_last(); 
    if (isset($error['type'], $error['message'], $error['file'], $error['line'])) { 
        errorHandler($error['type'],$error['message'],$error['file'],$error['line']);
    }elseif ($error['type'] == 1) {
        dump($error);
    }
}

function handleJsonError($file, $line) 
{
	$jsonErrorNr = json_last_error();
	$errstr = '';
	switch ($jsonErrorNr) {
        case JSON_ERROR_NONE:
            $errstr .= ' - No errors' . PHP_EOL;
        break;
        case JSON_ERROR_DEPTH:
            $errstr .= ' - Maximum stack depth exceeded' . PHP_EOL;
        break;
        case JSON_ERROR_STATE_MISMATCH:
            $errstr .= ' - Underflow or the modes mismatch' . PHP_EOL;
        break;
        case JSON_ERROR_CTRL_CHAR:
            $errstr .= ' - Unexpected control character found' . PHP_EOL;
        break;
        case JSON_ERROR_SYNTAX:
            $errstr .= ' - Syntax error, malformed JSON' . PHP_EOL;
        break;
        case JSON_ERROR_UTF8:
            $errstr .= ' - Malformed UTF-8 characters, possibly incorrectly encoded' . PHP_EOL;
        break;
        default:
            $errstr = ' - Unknown error' . PHP_EOL;
        break;
    }
	errorHandler ($jsonErrorNr, $errstr, $file, $line);
}

function renderError ($message, $file, $line, $code, $trace, $httpHeader = 'HTTP/1.0 500 Internal Server Error') {
	$file_lines = file_exists($file) ? file($file) : array();
    $range = ($line - 15) < 0 ? range(1, 30) : range($line - 15, $line + 15);
    $lines = array();

    foreach ($range as $line_number) {
        if(isset($file_lines[$line_number-1])) {
            $lines[$line_number] = $file_lines[$line_number-1];
        }
    }
	if (ob_get_contents()) ob_end_clean();
	$error = array(
		'message' 		=> $message,
		'file' 			=> $file,
		'line' 			=> $line,
		'code' 			=> $code,
		'lines' 		=> $lines,
		'trace' 		=> $trace,
		'httpHeader' 	=> $httpHeader,
	);
	if (file_exists(realpath(__DIR__) . '/errorview.php')) {
		include(realpath(__DIR__) . '/errorview.php');
	} else {
		header('Content-type: application/json');
		die(json_encode($error));
	}
	exit;
}
?>