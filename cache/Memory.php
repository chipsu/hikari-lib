<?php

namespace hikari\cache;

class Memory extends CacheAbstract implements CacheInterface {
    use \hikari\storage\ArrayTrait;
    public $data;

    function __construct(array $parameters = []) {
        parent::__construct($parameters);
    }
}
