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
    public static $nextId = 1;

    function attributes(array $attributes) {
        $result = [];
        foreach($attributes as $key => $value) {
            if(isset(static::$booleanAttributes[$key])) {
                if($value) {
                    $result[] = htmlspecialchars($key);
                }
            } else if($value !== null) {
                $result[] = sprintf('%s=\'%s\'', htmlspecialchars($key), is_scalar($value) ? htmlspecialchars($value) : json_encode($value));
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

    function a($href, array $attributes = [], $content = null) {
        $attributes = array_merge($attributes, ['href' => $href]);
        return $this->tag('a', $attributes, $content !== null ? $content : $href);
    }

    function img($src, array $attributes = []) {
        return $this->tag('img', array_merge($attributes, [
            'src' => $src,
        ]));
    }

    function id() {
        return '_hi_' . static::$nextId++;
    }

    function input($name, $type, array $attributes = []) {
        $attributes = array_merge($attributes, ['name' => $name, 'type' => $type]);
        return $this->autoLabel($attributes, 'before') . $this->tag('input', $attributes) . $this->autoLabel($attributes, 'after');
    }

    function text($name, array $attributes = [], $value = null) {
        return $this->input($name, 'text', array_merge($attributes, ['value' => $value]));
    }

    function password($name, array $attributes = [], $value = null) {
        return $this->input($name, 'password', array_merge($attributes, ['value' => $value]));
    }

    function submit($name, array $attributes = [], $value = null) {
        return $this->input($name, 'submit', array_merge($attributes, ['value' => $value === null ? $name : $value]));
    }

    function reset($name, array $attributes = [], $value = null) {
        return $this->input($name, 'reset', array_merge($attributes, ['value' => $value === null ? $name : $value]));
    }

    function button($name, array $attributes = [], $value = null) {
        return $this->input($name, 'button', array_merge($attributes, ['value' => $value === null ? $name : $value]));
    }

    function textarea($name, array $attributes = [], $content = null) {
        $attributes = array_merge($attributes, ['name' => $name]);
        return $this->autoLabel($attributes, 'before') . $this->tag('textarea', $attributes, $content) . $this->autoLabel($attributes, 'after');
    }

    function label($title, $for = null, array $attributes = []) {
        return $this->tag('label', array_merge($attributes, ['for' => $for]), $title);
    }

    function autoLabel(&$attributes, $position) {
        if(!empty($attributes['label'])) {
            if($position == (empty($attributes['labelPosition']) ? 'before' : $attributes['labelPosition'])) {
                if(empty($attributes['id'])) {
                    $attributes['id'] = $this->id();
                }
                return $this->label($attributes['label'], $attributes['id']);
            }
        }
        return '';
    }
}
