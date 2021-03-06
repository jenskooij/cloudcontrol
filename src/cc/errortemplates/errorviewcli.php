------------------------------------------------------------------------------------------------------------------------------------------------
| THE FOLLOWING ERROR OCCURED                                                                                                                  |
------------------------------------------------------------------------------------------------------------------------------------------------

<?php echo $message . PHP_EOL; ?>

------------------------------------------------------------------------------------------------------------------------------------------------
| IN FILE                                                                                                                                      |
------------------------------------------------------------------------------------------------------------------------------------------------

<?php echo $file . ':' . $line . PHP_EOL; ?>

------------------------------------------------------------------------------------------------------------------------------------------------
| CONTENTS OF THE FILE                                                                                                                         |
------------------------------------------------------------------------------------------------------------------------------------------------

<?php
foreach ($lines as $nr => $currentLine) {
    echo ($nr == $line ? '* ' : '  ') . str_pad($nr, 3, "0", STR_PAD_LEFT) . ' ' . $currentLine;
}
?>

------------------------------------------------------------------------------------------------------------------------------------------------
| STACK TRACE                                                                                                                                  |
------------------------------------------------------------------------------------------------------------------------------------------------

<?php
foreach ($trace as $row) {
    echo (isset($row['file']) ? basename($row['file']) : '') . ':'
        . (isset($row['line']) ? $row['line'] : '') . "\t\t\t"
        . (isset($row['class']) ? $row['class'] : ' ') . "\t\t\t"
        . (isset($row['type']) ? $row['type'] : ' ') . "\t\t\t"
        . (isset($row['function']) ? $row['function'] : ' ') . PHP_EOL;
}
?>