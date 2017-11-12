<?php
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST');
    header('Access-Control-Allow-Headers: Content-Type');

    $number = $_REQUEST['number'];
    if (!is_numeric($number) || !is_float((float)$number)) {
        echo json_encode('Bad Request');
        exit(1);
    }

    require_once ('vendor/autoload.php');
    require_once ('config/config.php');

    use src\Application;

    $app = new Application();
    $result = $app->prognosis($number);
    echo $result;

