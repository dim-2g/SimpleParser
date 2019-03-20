<?php

namespace Core;

class Utils
{
    public static function file_put_contents_force($filename, $content, $flags = 0)
    {
        self::createDirectoriesPath($filename);
        return file_put_contents($filename, $content, $flags);
    }

    private static function createDirectoriesPath($url)
    {
        $result = true;
        if (!self::isPathToFileExists($url)) {
            $directoryPath = dirname($url);
            $result = mkdir($directoryPath.'/',0755,true);
        }
        return $result;
    }

    private static function isPathToFileExists($url)
    {
        $directoryPath = dirname($url);
        if (is_readable($directoryPath) && is_dir($directoryPath)) {
            return true;
        } else {
            return false;
        }
    }
}