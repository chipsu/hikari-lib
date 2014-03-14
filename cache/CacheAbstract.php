<?php

namespace hikari\cache;

abstract class CacheAbstract extends \hikari\component\Component implements CacheInterface {
    use \hikari\storage\StorageTrait;
}