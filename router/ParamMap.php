<?php

namespace hikari\router;

use \hikari\core\Component;

class ParamMap extends Component {
    public $map = [];

    public function replace(array $input) {
        $result = $input;

        foreach($this->map as $key => $items) {
            if(!isset($input[$key])) {
                continue;
            }

            $fail = false;
            $param = $input[$key];

            if(isset($items[$input[$key]])) {
                $param = $items[$input[$key]];
            } else if(isset($items['*'])) {
                $param = $items['*'];
            } else {
                continue;
            }

            $param = preg_replace_callback('/\:(?<key>\w+)/', function($match) use($input, &$fail) {
                $key = lcfirst($match['key']);
                if(!isset($input[$key])) {
                    $fail = true;
                    return $match['key'];
                }
                $result = $input[$key];
                if(strtoupper($match['key'][0]) === $match['key'][0]) {
                    $result = ucfirst($result);
                }
                return $result;
            }, $param);

            if(!$fail) {
                $result[$key] = $param;
            }
        }

        return $result;
    }
}