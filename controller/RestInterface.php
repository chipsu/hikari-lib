<?php

namespace hikari\controller;

interface RestInterface extends ModelInterface {
    function get();
    function put();
    function post();
    function delete();
}
