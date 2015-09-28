<?php

namespace hikari\renderer;

class Json extends RendererAbstract {
    public $options = \JSON_PRETTY_PRINT;

    public function render($event) {
        $event->result = json_encode($event->result, $this->options);
        return true;
    }

    public function getContentType() {
        return 'application/json';
    }
}