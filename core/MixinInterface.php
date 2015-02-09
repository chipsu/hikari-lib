<?php

namespace hikari\core;

interface MixinInterface {
    function attach($context);
    function detach();
}