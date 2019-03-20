<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

require 'Core/Autoload.php';

use Core\CsvReader;
use Core\SimpleParser;
use Core\Category;

$category = new Category();
echo $category->findIdByName('Кожухи для видеокамер (Ex)');
die();

$reader = new CsvReader('data/import.csv');
//$reader->run(50,500);

$reader->getUniqFields(array('category', 'brand'));
//print_r($data);
