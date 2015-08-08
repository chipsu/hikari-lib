<?php

namespace hikari\router;

use \hikari\core\Component;

class ParamMap extends Component {
    public $map = [];

    public function replace(array $input) {
        $result = [];

        foreach($this->map as $key => $items) {
            if(!isset($input[$key])) {
                continue;
            }

            if(isset($items[$input[$key]])) {
                $result[$key] = $items[$input[$key]];
            } else if(isset($items['*'])) {
                $result[$key] = $items['*'];
            } else {
                continue;
            }

            $result[$key] = preg_replace_callback('/\:(?<key>\w+)/', function($match) use($input) {
                $result = $input[lcfirst($match['key'])];
                if(strtoupper($match['key'][0]) === $match['key'][0]) {
                    $result = ucfirst($result);
                }
                return $result;
            }, $result[$key]);
        }

        return $result;
    }
}