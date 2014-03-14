<?php

namespace hikari\storage;

abstract class ArrayAbstract extends StorageAbstract {
    use ArrayTrait;

    function __construct(&$array) {
        $this->bind($array);
    }
}
