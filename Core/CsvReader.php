<?php

namespace Core;

use Core\Product;

class CsvReader
{
    public $file;
    public $handle;
    public $product;
    public $products;
    public $cache_products;

    public function __construct($file)
    {
        $this->file = $file;
        $this->handle = fopen($file, "r");
    }

    public function run($limit=10, $offset=0)
    {
        $counter = 0;
        foreach ($this->getNextRow() as $iter => $row) {
            if ($iter == 0) {
                continue;
            }
            if ($iter < $offset) {
                continue;
            }

            $this->product = new Product();
            $rowValues = explode(';', $row[0]);
            $this->product->setProductValuesFromArray([
                'article',
                'partnumber',
                'category',
                'brand',
                'name',
                'price',
                'price_small',
                'currency',
            ], $rowValues);

            $parser = new SimpleParser($this->product);
            $this->product = $parser->parseOnePageByArticle();

            $this->product->saveProductToCache();
            echo "Обрабатываю строку {$iter}. Артикул {$this->product->article}\n";
            //print_r($this->product);
            //usleep(200000); //0.2s
            $counter++;
            if ($counter >= $limit) {
                break;
            }
        }
    }

    public function getDataFromCsv()
    {
        foreach ($this->getNextRow() as $iter => $row) {
            if ($iter == 0) {
                continue;
            }
            $product = new Product();
            $rowValues = explode(';', $row[0]);
            if (empty($row[0])) {
                continue;
            }
            $product->setProductValuesFromArray([
                'article',
                'partnumber',
                'category',
                'brand',
                'name',
                'price',
                'price_small',
                'currency',
            ], $rowValues);
            $this->products[] = $product;
        }
        return $this->products;
    }

    public function getUniqFields($findFields)
    {
        $uniqValues = [];
        foreach ($findFields as $item) {
            $uniqValues[$item] = [];
        }
        $products = $this->getDataFromCsv();
        foreach ($products as $product) {

            foreach ($uniqValues as $key => $value) {
                $uniqValues[$key] = $this->addValueToUniqArray($uniqValues[$key], $product->$key);
            }
        }
        print_r($uniqValues);


    }

    private function addValueToUniqArray($array, $value)
    {
        if (empty($value)) {
            return $array;
        }
        if (!in_array($value, $array)) {
            $array[] = $value;
        }
        return $array;
    }

    public function getNextRow()
    {
        while (feof($this->handle) === false) {
            yield fgetcsv($this->handle);
        }
    }

    public function __destruct()
    {
        fclose($this->handle);
    }

    public function findProductFromCache()
    {

        $this->cache_products = [];

        foreach ($this->getDataFromCsv() as $product) {
            $this->cache_products[] = $product->findOneFromCache();
        }
        return $this->cache_products;
    }

    public function printPHP()
    {
        echo 'PHP просто супер.' . PHP_EOL;
    }
}