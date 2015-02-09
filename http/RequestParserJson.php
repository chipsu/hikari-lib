<?php

namespace hikari\http;

class RequestParserJson implements RequestParserInterface {
    function parse($request) {
        return json_decode($request->getBody());
    }
}