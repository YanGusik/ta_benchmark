<?php

require_once '/home/edmond/ta_benchmark/trueasync/vendor/autoload.php';

$app = require_once '/home/edmond/ta_benchmark/trueasync/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Warmup — establish connection
DB::selectOne('SELECT 1');

$iterations = (int)($argv[1] ?? 10);

// Start profiling after bootstrap
if (function_exists('callgrind_control')) {
    callgrind_control(CALLGRIND_ZERO_STATS);
}

for ($i = 0; $i < $iterations; $i++) {
    $userId = rand(1, 100);

    // 1. SELECT user
    $user = DB::selectOne('SELECT id, name, email FROM users WHERE id = ?', [$userId]);

    // 2. SELECT posts
    $posts = DB::select(
        'SELECT id, title, views_count FROM posts WHERE user_id = ? ORDER BY created_at DESC LIMIT 10',
        [$userId]
    );

    // 3. INSERT post_view
    $postId = !empty($posts) ? $posts[0]->id : 1;
    DB::insert(
        'INSERT INTO post_views (post_id, ip_address, viewed_at) VALUES (?, ?, NOW())',
        [$postId, '10.0.' . rand(0, 255) . '.' . rand(0, 255)]
    );

    // 4. UPDATE counter
    DB::update('UPDATE posts SET views_count = views_count + 1 WHERE id = ?', [$postId]);

    // 5. SELECT aggregate
    $stats = DB::selectOne(
        'SELECT COUNT(*) as total_views, MAX(viewed_at) as last_view FROM post_views WHERE post_id = ?',
        [$postId]
    );
}

echo "Done: {$iterations} iterations\n";
