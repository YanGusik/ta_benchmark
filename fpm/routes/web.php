<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/hello', fn() => response()->json([
    'message' => 'Hello from FPM!',
    'time'    => now()->toIso8601String(),
]));

Route::get('/test', function () {
    DB::select('SELECT pg_sleep(0.01);');
    return response()->json(['ok' => true, 'time' => now()->toIso8601String()]);
});
