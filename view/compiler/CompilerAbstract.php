<?php

namespace hikari\view\compiler;

abstract class CompilerAbstract implements CompilerInterface {
    public $debug = HI_DEBUG;

    function file($fileName, array $options = []) {
        $source = file_get_contents($fileName);
        return $this->source($source, $options);
    }
    function store($fileName, $result) {
        file_put_contents($fileName, $result);
    }
}