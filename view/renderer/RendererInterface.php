<?php

namespace hikari\view\renderer;

interface RendererInterface {
    function render($source, array $data, array $options);
}