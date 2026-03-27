<?php

$server = new Swoole\Http\Server('0.0.0.0', 8086);

$server->set([
    'worker_num' => 4,
    'enable_coroutine' => true,
]);

$server->on('request', function (Swoole\Http\Request $request, Swoole\Http\Response $response) {
    $response->header('Content-Type', 'application/json');
    $response->end(json_encode([
        'message' => 'Hello from Swoole Async!',
        'uri' => $request->server['request_uri'],
        'memory' => round(memory_get_usage(true) / 1024 / 1024, 2),
        'timestamp' => date('Y-m-d H:i:s'),
    ]));
});

echo "Swoole HTTP server started on 0.0.0.0:8086 (4 workers, coroutines enabled)\n";
$server->start();
