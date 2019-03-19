<?php

namespace Core;

class Logger
{
    public $file;

    function __construct($filename)
    {
        $this->file = 'logs/' . $filename;
    }

    public function write($text)
    {
        $date = new \DateTime();
        $text = $date->format('Y-m-d H:i:s') . ' ' . $text;
        file_put_contents($this->file, $text . PHP_EOL, FILE_APPEND);
    }
}