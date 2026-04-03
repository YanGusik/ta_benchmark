<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $password = Hash::make('password');
        $users = [];
        for ($i = 1; $i <= 100; $i++) {
            $users[] = [
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'email_verified_at' => $now,
                'password' => $password,
                'remember_token' => Str::random(10),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        foreach (array_chunk($users, 50) as $chunk) {
            DB::table('users')->insert($chunk);
        }

        // Create 10 posts per user
        $posts = [];
        $now = now();
        for ($userId = 1; $userId <= 100; $userId++) {
            for ($i = 0; $i < 10; $i++) {
                $posts[] = [
                    'user_id' => $userId,
                    'title' => "Post {$i} by user {$userId}",
                    'body' => str_repeat("Lorem ipsum dolor sit amet. ", 10),
                    'views_count' => rand(0, 1000),
                    'created_at' => $now->copy()->subDays(rand(0, 365)),
                    'updated_at' => $now,
                ];
            }
        }
        foreach (array_chunk($posts, 500) as $chunk) {
            DB::table('posts')->insert($chunk);
        }

        $this->command->info('Seeded 100 users + 1000 posts');
    }
}
