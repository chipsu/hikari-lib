<?php

namespace hikari\html;

class Html extends \hikari\component\Component {

    // TODO: special cases..
    public function attributes(array $attributes) {    
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

    public function tag($name, array $attributes = [], $content = null) {
        return $this->open($name, $attributes) . $content . $this->close($name);
    }

    public function open($name, array $attributes = []) {
        return sprintf('<%s%s>', htmlspecialchars($name), $this->attributes($attributes));
    }

    public function close($name) {
        return sprintf('</%s>', htmlspecialchars($name));
    }
}
