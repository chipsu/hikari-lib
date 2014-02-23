<?php

namespace hikari\http;

use \hikari\component\Component as Component;

class Response extends Component {

    public function code($code) {
        http_response_code($code);
    }
}
