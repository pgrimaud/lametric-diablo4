<?php

use LaMetric\{Api, Response};

use GuzzleHttp\Client as GuzzleClient;
use Predis\Client as PredisClient;

require_once __DIR__ . '/../vendor/autoload.php';

//header('Content-Type: application/json');

$response = new Response();

try {
    $api = new Api(new GuzzleClient(), new PredisClient());
    $frames = $api->fetchData();

    echo $response->printData($frames);
} catch (Exception $exception) {
    echo $response->printError($exception->getMessage());
}
