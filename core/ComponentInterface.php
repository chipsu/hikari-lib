<?php

namespace hikari\core;

interface ComponentInterface {
    function init();
    function getApplication();
    function getConfig();
    function mixins();
    function attachMixin($name, $mixin);
    function detachMixin($name);
}

