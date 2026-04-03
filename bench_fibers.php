<?php

$count = (int)($argv[1] ?? 10000);
$switches = (int)($argv[2] ?? 10);

echo "Spawning {$count} coroutines, each does {$switches} suspends...\n";

$start = hrtime(true);

$coroutines = [];
for ($i = 0; $i < $count; $i++) {
    $coroutines[] = Async\spawn(function () use ($switches) {
        for ($j = 0; $j < $switches; $j++) {
            Async\suspend();
        }
    });
}

Async\await_all_or_fail($coroutines);

$elapsed = (hrtime(true) - $start) / 1_000_000;
$total_switches = $count * $switches;

printf("Done: %d coroutines × %d switches = %d total context switches\n", $count, $switches, $total_switches);
printf("Time: %.2f ms\n", $elapsed);
printf("Speed: %.0f switches/ms (%.0f M switches/s)\n", $total_switches / $elapsed, $total_switches / $elapsed / 1000);
