<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

require 'Core/Autoload.php';

use Core\SimpleParser;

$article = 125549;
if (!empty($argv[1])) {
    $article = $argv[1];
}
$parser = new SimpleParser();
$page = $parser->parseOnePageByArticle($article);

print_r($page);