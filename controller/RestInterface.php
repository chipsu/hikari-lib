<?php

namespace hikari\controller;

interface RestInterface extends ModelInterface {
    function head();
    function get();
    function put();
    function post();
    function patch();
    function delete();
    function options();
}
