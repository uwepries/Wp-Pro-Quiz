<?php

$poMsgIdAndMsgStrRegex = '/^#\s*(.+?)\nmsgid "(.+?)"\nmsgstr "(.+?)"/m';
$content = file_get_contents(__DIR__ . "/wp-pro-quiz-de_DE.po");

preg_match_all($poMsgIdAndMsgStrRegex, $content, $matches);
var_dump($matches);
