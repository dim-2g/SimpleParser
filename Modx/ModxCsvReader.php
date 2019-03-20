<?php

namespace Modx;

use Core\Product;

class ModxCsvReader extends \Core\CsvReader
{
    public $modx;
    public $cache_products;

    public function __construct($file)
    {
        global $modx;
        parent::__construct($file);
        $this->modx = &$modx;
    }

    public function importByCategoryName($categoryName)
    {
        echo $categoryName;
    }

}