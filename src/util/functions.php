<?php

/**
 * Alias for GlobalFunctions::dump
 */
function dump()
{
    $debug_backtrace = current(debug_backtrace());
    \CloudControl\Cms\util\GlobalFunctions::dump($debug_backtrace);
}