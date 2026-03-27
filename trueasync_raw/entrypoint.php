<?php

use FrankenPHP\HttpServer;
use FrankenPHP\Request;
use FrankenPHP\Response;

set_time_limit(0);

HttpServer::onRequest(function (Request $request, Response $response) {
    $response->setStatus(200);
    $response->setHeader('Content-Type', 'application/json');
    $response->write(json_encode([
        'message' => 'Hello from TrueAsync!',
        'uri' => $request->getUri(),
        'memory' => round(memory_get_usage(true) / 1024 / 1024, 2),
        'timestamp' => date('Y-m-d H:i:s'),
    ]));
    $response->end();
});
