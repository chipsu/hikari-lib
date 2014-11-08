<?php

namespace hikari\core;

class File {

    static function getUserFile($baseName, $exists = true, $suffix = 'user') {
        $info = pathinfo($baseName);
        $file = $info['dirname'] . '/' . $info['filename'] . '.' . $suffix . '.' . $info['extension'];
        if($exists && !is_file($file))
            return false;
        return $file;
    }

    static function ensureDirectoryExists($dir, $parent = true) {
        if(!is_dir($dir)) {
            mkdir($dir, 0775, $parent) or \hikari\exception\Core::raise('Could not create directory "%s"', $dir);
        }
    }
}