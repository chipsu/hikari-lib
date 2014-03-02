<?php

namespace hikari\router;

use \hikari\component\Component as Component;

class Match extends Component {
    public $controller;
    public $action;
    public $request;
}