<?php

namespace hikari\controller;

interface CrudInterface extends ModelInterface {
    function create();
    function read();
    function update();
    function dispose();
}
