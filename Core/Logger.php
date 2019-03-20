<?php

namespace Core;

use Core\Utils;

class Logger
{
    public $file;
    public $root;

    function __construct($filename)
    {
        $this->root = dirname(__DIR__);
        $this->file = $this->root . '/logs/' . $filename;
    }

    public function write($text)
    {
        $date = new \DateTime();
        $text = $date->format('Y-m-d H:i:s') . ' ' . $text;
        Utils::file_put_contents_force($this->file, $text . PHP_EOL, FILE_APPEND);
    }
}