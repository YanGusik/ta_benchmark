<?php

$concurrency = (int)($argv[1] ?? 20);
$rounds = (int)($argv[2] ?? 3);

$pdo = new PDO('pgsql:host=127.0.0.1;port=5434;dbname=laravel', 'laravel', 'secret', [
    PDO::ATTR_POOL_ENABLED => true,
    PDO::ATTR_POOL_MIN => 2,
    PDO::ATTR_POOL_MAX => 10,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

// Warmup
$pdo->query('SELECT 1');

echo "Benchmarking: {$concurrency} concurrent coroutines × {$rounds} rounds\n";

$start = hrtime(true);

for ($round = 0; $round < $rounds; $round++) {
    $coroutines = [];

    for ($i = 0; $i < $concurrency; $i++) {
        $coroutines[] = Async\spawn(function () use ($pdo) {
            $userId = rand(1, 100);

            $stmt = $pdo->prepare('SELECT id, name, email FROM users WHERE id = ?');
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_OBJ);

            $stmt = $pdo->prepare('SELECT id, title, views_count FROM posts WHERE user_id = ? ORDER BY created_at DESC LIMIT 10');
            $stmt->execute([$userId]);
            $posts = $stmt->fetchAll(PDO::FETCH_OBJ);

            $postId = !empty($posts) ? $posts[0]->id : 1;

            $stmt = $pdo->prepare('INSERT INTO post_views (post_id, ip_address, viewed_at) VALUES (?, ?, NOW())');
            $stmt->execute([$postId, '10.0.' . rand(0, 255) . '.' . rand(0, 255)]);

            $stmt = $pdo->prepare('UPDATE posts SET views_count = views_count + 1 WHERE id = ?');
            $stmt->execute([$postId]);

            $stmt = $pdo->prepare('SELECT COUNT(*) as total_views, MAX(viewed_at) as last_view FROM post_views WHERE post_id = ?');
            $stmt->execute([$postId]);
            $stats = $stmt->fetch(PDO::FETCH_OBJ);
        });
    }

    Async\await_all_or_fail($coroutines);
    echo "Round " . ($round + 1) . " done\n";
}

$elapsed = (hrtime(true) - $start) / 1_000_000;
$total = $concurrency * $rounds;

printf("Done: %d coroutines, %.2f ms (%.2f ms/coroutine)\n", $total, $elapsed, $elapsed / $total);
