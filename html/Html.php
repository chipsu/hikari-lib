<?php

namespace hikari\html;

class Html extends \hikari\component\Component {

    // TODO: special cases..
    function attributes(array $attributes) {    
        $result = [];
        $special = ['checked' => 1, 'disabled' => 1, 'readonly' => 1, 'selected' => 1];
        foreach($attributes as $key => $value) {
            if(isset($special[$key])) {
                if($value)
                    $result[] = htmlspecialchars($key);
            } else if($value !== null) {
                $result[] = sprintf('%s="%s"', htmlspecialchars($key), htmlspecialchars($value));
            }
        }
        return $result ? ' ' . implode(' ', $result) : '';
    }

    function tag($name, array $attributes = [], $content = null) {
        return $this->open($name, $attributes) . $content . $this->close($name);
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
