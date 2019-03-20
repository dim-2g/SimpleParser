<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

require 'Core/Autoload.php';

use Modx\CsvReader;

define('MODX_API_MODE', true);
require_once $_SERVER['DOCUMENT_ROOT'] . '/index.php';
$modx->getService('error','error.modError');
$modx->setLogLevel(modX::LOG_LEVEL_ERROR);
$modx->setLogTarget('FILE');

$reader = new CsvReader('data/import.csv');

$reader->importByCategoryName('GSM мониторинг');
//$reader->printPHP();
//$data = $reader->findProductFromCache();

/*
echo '<pre>';
var_dump($data);
echo '</pre>';
*/