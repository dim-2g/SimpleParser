<?php

namespace Core;

use Core\Option;

class Product
{
    public $remote_url;
    public $article;
    public $partnumber;
    public $category;
    public $brand;
    public $name;
    public $price;

    /*
     * мелкий опт
     */
    public $price_small;
    public $currency;

    public $available;
    public $warranty;
    public $description;
    public $complectation;
    public $options;
    public $images;


    function __construct()
    {
        $this->options = [];
        $this->root = dirname(__DIR__);
        $this->cache_path = $this->root . '/cache_elements';
        $this->log = new Logger('product.txt');
    }

    public function addOptions($name, $value)
    {
        $this->options[$name] = new Option($name, $value);
    }

    public function toString($toArray = false)
    {
        if ($toArray) {
            return print_r($this);
        } else {
            return var_export($this);
        }

    }

    public function toJson()
    {
        return json_encode($this);
    }

    public function set($name, $value)
    {
        if (!is_array($value)) {
            $value = trim($value);
        }
        $this->$name = $value;
    }

    public function debug($toArray)
    {
        echo '<pre>';
        $this->toString($toArray);
        echo '</pre>';
    }

    public function setProductValuesFromArray($keys, $values)
    {
        foreach ($keys as $index => $name) {
            if (!empty($values[$index])) {
                $this->set($name, $values[$index]);
            }
        }
    }

    public function saveProductToCache()
    {
        $this->log->write('findCacheName(): сохраняем документ с артикулом = ' . $this->article);

        $filename = $this->findCacheName();
        Utils::file_put_contents_force($filename, print_r($this, true));

        $filenameJson = $this->findCacheNameJson();
        Utils::file_put_contents_force($filenameJson, $this->toJson());
    }

    public function findCacheName()
    {
        return $filename = $this->cache_path . '/' . $this->article . '.txt';
    }

    public function findCacheNameJson()
    {
        return $filename = $this->cache_path . '/' . $this->article . '.json';
    }

    public function findOneFromCache()
    {
        $filename = $this->findCacheNameJson();
        $jsonData = file_get_contents($filename);
        return json_decode($jsonData);
    }
}