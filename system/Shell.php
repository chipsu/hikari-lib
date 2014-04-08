<?php

namespace hikari\system;

use \hikari\component\Component;

class Shell extends Component {
    public $output = [];
    public $result;

    function run($command, array $args = []) {
        $command = escapeshellcmd($command);
        $args = array_map('escapeshellarg', $args);
        $exec = $command . ' ' . implode(' ', $args) . ' 2>&1';
        $exec = '/bin/bash -c ' . escapeshellarg($exec);
        exec($exec, $this->output, $this->result);
        return $this->result == 0;
    }

    function __toString() {
        return implode(PHP_EOL, $this->output);
    }
}