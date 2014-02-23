<?php

namespace hikari\utilities;

class File {
    
    public static function getUserFile($baseName, $exists = true, $suffix = 'user') {
        $info = pathinfo($baseName);
        $file = $info['dirname'] . '/' . $info['filename'] . '.' . $suffix . '.' . $info['extension'];
        if($exists && !is_file($file))
            return false;
        return $file;
    }

}