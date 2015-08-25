<?php

namespace hikari\http;

use \hikari\core\Component as Component;

class Response extends Component {

    function code($code) {
        http_response_code($code);
    }

    function redirect($url) {
        $this->header('Location', $url);
        die;
    }

    function header($key, $value, $replace = true) {
        header($key . ': ' . $value, $replace);
    }
}
