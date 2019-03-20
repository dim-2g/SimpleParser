<?php

namespace Modx;

use Core\Product;

class CsvReader extends \Core\CsvReader
{
    public $modx;
    public $cache_products;

    public function __construct($file)
    {
        global $modx;
        parent::__construct($file);
        $this->modx = &$modx;
        $this->findProductFromCache();
    }

    public function importByCategoryName($categoryName)
    {
        foreach ($this->cache_products as $product) {
            if ($product->category != $categoryName) {
                continue;
            }
            echo '<pre>';
            print_r($product);
            echo '</pre>';
        }
    }
}