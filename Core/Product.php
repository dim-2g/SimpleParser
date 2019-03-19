<?php

namespace Core;

use Core\Option;

class Product
{
    public $price;
    public $available;
    public $warranty;
    public $description;
    public $complectation;
    public $options;
    public $images;

    function __construct()
    {
        $this->options = [];
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

    public function debug($toArray)
    {
        echo '<pre>';
        $this->toString($toArray);
        echo '</pre>';
    }
}