<?php

namespace hikari\filter;

use hikari\core\Mixin;

class Response extends Filter {
    private $_formats = [];
    private $_formatter;

    function getFormats() {
        return $this->_formats;
    }

    function setFormats($formats) {
        $this->_formats = $formats;
    }

    function beforeAction($event) {
        $accept = $event->controller->request->header('accept', null, true);
        $accept[]  = '*'; // FIXME
        $formats = $this->getFormats();
        foreach($accept as $format) {
            if(isset($formats[$format])) {
                $this->_formatter = static::createInstance($formats[$format]);
                break;
            }
        }
        return true;
    }

    function afterAction($event) {
        if($this->_formatter) {
            if($this->_formatter->run($event)) {
                $event->headers['Content-Type'] = $this->_formatter->contentType;
                $event->handled = true;
            }
        }
        return true;
    }
}
