<?php

namespace hikari\renderer;

class Text extends RendererAbstract {

    function render($event) {
        $event->result = gettype($event->result);
        return true;
    }

    public function getContentType() {
        return 'text/plain';
    }
}