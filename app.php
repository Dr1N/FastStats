<?php
    set_time_limit(0);

    require_once ('vendor/autoload.php');
    require_once ('config/config.php');

    use src\Application;

    $app = new Application();
    $app->parsing();

