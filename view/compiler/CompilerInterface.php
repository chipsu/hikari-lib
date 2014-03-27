<?php

namespace hikari\view\compiler;

interface CompilerInterface {
    function file($fileName, array $options = []);
    function source($source, array $options = []);
    function store($fileName, $result);
}
