<?php
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
        ob_end_clean();
        ob_start();
        echo <<<END
<!DOCTYPE html>
<html>
<head>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.0.0/highlight.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.0.0/styles/default.min.css" />
<script>hljs.initHighlightingOnLoad();</script>
<style>code.php.hljs{margin: -0.4em 0;}code.active{background-color:#e0e0e0;}code:before{content:attr(data-line);}code.active:before{color:#c00;font-weight:bold;}</style>
</head>
<body>
END;

        echo '<div>Dump: ' . $debug_backtrace['file'] . ':<b>' . $debug_backtrace['line'] . "</b></div>";
        echo '<pre>';
        foreach (func_get_args() as $data) {
            echo "<code>";
            var_dump($data);
            echo "</code>";
        }
        echo '</pre>';
        echo <<<END
</body>
</html>
END;
    }
    die;
}

/**
 * Minify the html for the outputbuffer
 *
 * @param $buffer
 * @return mixed
 */
function sanitize_output($buffer)
{
    if (!isset($_GET['unsanitized'])) {
        $search = array(
            '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
            '/[^\S ]+\</s',     // strip whitespaces before tags, except space
            '/(\s)+/s',         // shorten multiple whitespace sequences
            '/<!--(.|\s)*?-->/' // Remove HTML comments
        );

        $replace = array(
            '>',
            '<',
            '\\1',
            ''
        );

        $buffer = preg_replace($search, $replace, $buffer);

        return $buffer;
    } else {
        return $buffer;
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
    array_walk_recursive($array, function (&$item) {
        if (!mb_detect_encoding($item, 'utf-8', true)) {
            $item = utf8_encode($item);
        }
    });

    return $array;
}