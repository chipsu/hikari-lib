<?php

namespace hikari\html;

class Html extends \hikari\component\Component {
    public static $voidElements = [
        'area' => true,
        'base' => true,
        'br' => true,
        'col' => true,
        'embed' => true,
        'hr' => true,
        'img' => true,
        'input' => true,
        'keygen' => true,
        'link' => true,
        'menuitem' => true,
        'meta' => true,
        'param' => true,
        'source' => true,
        'track' => true,
        'wbr' => true,
    ];
    public static $booleanAttributes = [
        'checked' => true,
        'compact' => true,
        'declare' => true,
        'defer' => true,
        'disabled' => true,
        'ismap' => true,
        'multiple' => true,
        'nohref' => true,
        'noresize' => true,
        'noshade' => true,
        'nowrap' => true,
        'readonly' => true,
        'selected' => true,
    ];

    function attributes(array $attributes) {    
        $result = [];
        foreach($attributes as $key => $value) {
            if(isset(static::$booleanAttributes[$key])) {
                if($value) {
                    $result[] = htmlspecialchars($key);
                }
            } else if($value !== null) {
                $result[] = sprintf('%s="%s"', htmlspecialchars($key), htmlspecialchars($value));
            }
        }
        return $result ? ' ' . implode(' ', $result) : '';
    }

    function tag($name, array $attributes = [], $content = null) {
        $close = '';
        if($content !== null || !isset(static::$voidElements[$name])) {
            $close = $content . $this->close($name);
        }
        return $this->open($name, $attributes) . $close;
    }

    function open($name, array $attributes = []) {
        return sprintf('<%s%s>', htmlspecialchars($name), $this->attributes($attributes));
    }

    function close($name) {
        return sprintf('</%s>', htmlspecialchars($name));
    }

    function a($href, array $attributes = [], $content) {
        $attributes = array_merge($attributes, ['href' => $href]);
        return $this->tag('a', $attributes, $content);
    }

    function img($src, array $attributes = []) {
        return $this->tag('img', array_merge($attributes, [
            'src' => $src,
        ]));
    }
}
