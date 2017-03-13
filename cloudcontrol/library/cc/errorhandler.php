<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'ErrorHandlingUtil.php');
set_exception_handler('exceptionHandler');
set_error_handler('errorHandler');
register_shutdown_function('shutdownHandler');

/**
 * An uncaught exception will result in the rendering
 * of the exception as though it were an error
 *
 * @param $e
 */
function exceptionHandler ($e) {
	\library\cc\ErrorHandlingUtil::renderError($e->getMessage(),$e->getFile(),$e->getLine(),$e->getCode(),$e->getTrace());
}

/**
 * When an error occures, render it properly.
 *
 * @param $errno
 * @param $errstr
 * @param $errfile
 * @param $errline
 */
function errorHandler ($errno, $errstr, $errfile, $errline) {
	\library\cc\ErrorHandlingUtil::renderError($errstr,$errfile,$errline,$errno,debug_backtrace());
}

/**
 * When an error occurs that kills the process, still try
 * to show it using a shutdownHandler.
 */
function shutdownHandler () {
	$error = error_get_last(); 
    if (isset($error['type'], $error['message'], $error['file'], $error['line'])) { 
		\library\cc\ErrorHandlingUtil::renderError($error['message'],$error['file'],$error['line'], $error['type']);
    }elseif ($error['type'] == 1) {
        dump($error);
    }
}



