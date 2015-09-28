<?php

namespace hikari\renderer;

interface RendererInterface extends \hikari\core\ComponentInterface {
    function render($event);
    function getContentType();
}