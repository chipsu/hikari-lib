<?php

namespace hikari\utilities;

use \hikari\exception\Core as CoreException;

class Lexer {
    public $rules = [];

    function run($source) {
        if(empty($this->rules)) {
            CoreException::raise('No rules set');
        }
        $tokens = [];
        if(!is_array($source)) {
            $source = explode(PHP_EOL, $source);
        }
        foreach($source as $index => $line) {
            $offset = 0;
            while($offset < strlen($line)) {
                $result = $this->match($line, $index, $offset);
                if($result === false) {
                    $this->parseError($source, $index, $offset);
                }
                $tokens[] = $result;
                $offset += strlen($result['match']);
            }
        }
        return $tokens;
    }

    function match($line, $index, $offset) {
        $subject = substr($line, $offset);
        foreach($this->rules as $pattern => $token) {
            if(preg_match($pattern, $subject, $match)) {
                return [
                    'match' => $match[1],
                    'token' => $token,
                    'index' => $index,
                    'offset' => $offset,
                ];
            }
        }
        return false;
    }

    function parseError(array $source, $index, $offset) {
        CoreException::raise('Parse error on line %d:%d', $index + 1, $offset);
    }
}
