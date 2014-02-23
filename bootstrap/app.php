<?php

require_once '../lib/hikari/autoload.php';

try {
    $app = new \hikari\application\Application;
    $app->run();
} catch(Exception $ex) {
    echo '<pre>';
    echo htmlspecialchars($ex);
    echo '</pre>';
}
