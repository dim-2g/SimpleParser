<?php

namespace Core;

class Option
{
    public $name;
    public $value;

    function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }
}