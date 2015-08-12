<?php

namespace hikari\http;

use \hikari\core\Component as Component;

class Response extends Component {

    function code($code) {
        http_response_code($code);
    }

    function redirect($url) {
        header('Location: ' . $url);
        die;
    }
}
