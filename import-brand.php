<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

require 'Core/Autoload.php';

use Modx\Brand;

define('MODX_API_MODE', true);
require_once $_SERVER['DOCUMENT_ROOT'] . '/index.php';
$modx->getService('error','error.modError');
$modx->setLogLevel(modX::LOG_LEVEL_ERROR);
$modx->setLogTarget('FILE');


$brand = new Brand();
//$result = $brand->createAllBrands();
//$result = $brand->createOne('BOSCH');
$result = $brand->findOne('BOSCH');
var_dump($result->get('id'));