<?php

namespace hikari\core;

class Map {
    static function mergeArray(array $array1, array $array2) {
        $result = array();
        $arrays = func_get_args();
        foreach($arrays as $array) {
            foreach($array as $key => $value) {
                if(is_string($key)) {
                    if(is_array($value) && array_key_exists($key, $result) && is_array($result[$key])) {
                        $result[$key] = static::mergeArray($result[$key], $value);
                    } else {
                        $result[$key] = $value;
                    }
                } else {
                    $result[] = $value;
                }
            }
        }
        return $result;
    }
}
