<?php

namespace hikari\cache;

class Output {

    /**
     * <?php if($cache->begin([__FILE__, __LINE__], ['ttl' => 3600, 'vary' => 'session']) : ?>
     *     <p>This element was generated: <?=date('c')?></p>
     * <?php $cache->end(); endif; ?>
     *
     * @param $key string|array Unique cache key
     * @param $options array Cache options
     */
    function begin($key, array $options = []);
    function end();
}
