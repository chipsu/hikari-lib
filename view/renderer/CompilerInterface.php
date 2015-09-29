<?php

namespace hikari\view\renderer;

interface CompilerInterface {
    function file($fileName, array $options = []);
    function source($source, array $options = []);
    function store($fileName, $result);
}
