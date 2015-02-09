<?php

namespace hikari\filter;

use hikari\core\Mixin;

class Header extends Filter {
    public $languages = [];

    function beforeAction($event) {
        // TODO: if(isset($this->languages[header_lang])) setLang()
        return true;
    }

    function afterAction($event) {
        return true;
    }
}
