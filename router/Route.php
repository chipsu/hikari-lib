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
        foreach($this->regexp as $regexp) {
            if(preg_match($regexp, $request->uri->path, $match)) {
                echo "MATCH<br>";
                var_dump($match);
                die;
            }
        }
        return false;
    }

    function compile() {
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