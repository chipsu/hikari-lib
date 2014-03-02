<?php

namespace hikari\router;

use \hikari\component\Component as Component;

class Route extends Component {
    public $name;
    public $regexp;
    public $format;
    public $target;

    function __construct(array $parameters = []) {
        parent::__construct($parameters);
        $this->compile();
    }

    function match($request) {
        foreach($this->regexp as $index => $regexp) {
            if(preg_match($regexp, $request->uri->path, $match)) {
                foreach($this->target as $key => $value) {
                    if(!is_int($key) && !isset($match[$key])) {
                        $match[$key] = $value;
                    }
                }
                $result = new Match($match);
                $result->request = clone $request;
                $result->request->get = array_merge($result->request->get, $match);
                $result->controller = $this->target[0];
                // CamelCase Controller temp fix.
                $replace = function($variable) use($match) {
                    $key = strtolower($variable[1]);
                    if(isset($match[$key])) {
                        $replace = $match[$key];
                        if(strtoupper($key[0]) == $variable[1][0]) {
                            $replace = ucfirst($replace);
                        }
                        return $replace;
                    }
                    return false;
                };
                $result->controller = preg_replace_callback('/\:([\w]+)/', $replace, $result->controller);
                return $result;
            }
        }
        return false;
    }

    function compile() {
        if(empty($this->target)) {
            \hikari\exception\Argument('No target set');
        }
        if(empty($this->regexp)) {
            if(empty($this->format)) {
                \hikari\exception\Argument('Route need at least one Format or Regexp');
            }
            if(is_string($this->format)) {
                $this->format = [$this->format];
            }
            foreach($this->format as $format) {
                $replace = function($match) {
                    return sprintf('(?<%s>[\w]+)', $match[1]);
                };
                $regexp = '/^' . preg_replace_callback('/\\\:([\w]+)/', $replace, preg_quote($format, '/')) . '$/';
                $this->regexp[] = $regexp;
            }
        } else if(is_string($this->regexp)) {
            $this->regexp = [$this->regexp];
        }
    }
}