<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/hello', fn() => response()->json([
    'message' => 'Hello from TrueAsync!',
    'time'    => now()->toIso8601String(),
]));

Route::get('/test', function () {
    DB::select('SELECT pg_sleep(0.01);');
    return response()->json(['ok' => true, 'time' => now()->toIso8601String()]);
});

Route::get('/debug/connections', function () {
    // Total connections grouped by state
    $byState = DB::select("
        SELECT state, count(*) as cnt
        FROM pg_stat_activity
        WHERE datname = current_database()
        GROUP BY state
        ORDER BY cnt DESC
    ");

    // Per-application (client_addr) breakdown — useful to confirm connections from this app
    $byClient = DB::select("
        SELECT client_addr, state, count(*) as cnt
        FROM pg_stat_activity
        WHERE datname = current_database()
        GROUP BY client_addr, state
        ORDER BY cnt DESC
        LIMIT 20
    ");

    // PostgreSQL server limit
    $limit = DB::selectOne("SELECT current_setting('max_connections')::int AS max_connections");

    $total = array_sum(array_column($byState, 'cnt'));

    return response()->json([
        'max_connections' => $limit->max_connections,
        'total'           => $total,
        'by_state'        => $byState,
        'by_client'       => $byClient,
        'time'            => now()->toIso8601String(),
    ]);
});

Route::get('/bench', function () {
    $userId = rand(1, 100);

    // 1. SELECT user (simulate auth lookup)
    $user = DB::selectOne('SELECT id, name, email FROM users WHERE id = ?', [$userId]);

    // 2. SELECT posts with filter (list query)
    $posts = DB::select(
        'SELECT id, title, views_count FROM posts WHERE user_id = ? ORDER BY created_at DESC LIMIT 10',
        [$userId]
    );

    // 3. INSERT a post_view record (write)
    $postId = !empty($posts) ? $posts[0]->id : 1;
    DB::insert(
        'INSERT INTO post_views (post_id, ip_address, viewed_at) VALUES (?, ?, NOW())',
        [$postId, '10.0.' . rand(0, 255) . '.' . rand(0, 255)]
    );

    // 4. UPDATE views counter (write)
    DB::update('UPDATE posts SET views_count = views_count + 1 WHERE id = ?', [$postId]);

    // 5. SELECT aggregate (dashboard-style query)
    $stats = DB::selectOne(
        'SELECT COUNT(*) as total_views, MAX(viewed_at) as last_view FROM post_views WHERE post_id = ?',
        [$postId]
    );

    return response()->json([
        'user' => $user->name,
        'posts' => count($posts),
        'views' => $stats->total_views,
        'time' => now()->toIso8601String(),
    ]);
});
