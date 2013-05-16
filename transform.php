<?php
$file = $_SERVER['argv'][1];

$buffer = 9999999999999;
$delim = '<?xml version="1.0" encoding="UTF-8"?>';
$fp = fopen($file, 'r');
print stream_get_line($fp, $buffer, $delim);

$doc = simplexml_load_string(stream_get_line($fp, $buffer, $delim));
print_r($doc);