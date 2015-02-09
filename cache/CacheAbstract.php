<?php

namespace hikari\cache;

use \hikari\core\Component;

abstract class CacheAbstract extends Component implements CacheInterface {
    use \hikari\storage\StorageTrait;
}