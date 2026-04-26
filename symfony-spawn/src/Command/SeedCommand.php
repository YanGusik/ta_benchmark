<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:seed', description: 'Seed benchmark data')]
class SeedCommand extends Command
{
    public function __construct(private readonly Connection $connection)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Seeding users...');
        for ($i = 1; $i <= 100; $i++) {
            $this->connection->executeStatement(
                'INSERT INTO users (name, email) VALUES (?, ?) ON CONFLICT (email) DO NOTHING',
                ["User {$i}", "user{$i}@bench.test"]
            );
        }

        $output->writeln('Seeding posts...');
        $posts = [];
        for ($i = 1; $i <= 1000; $i++) {
            $posts[] = sprintf(
                "(%d, 'Post title %d', 'Body content for post %d. Lorem ipsum dolor sit amet.', %d)",
                rand(1, 100), $i, $i, rand(0, 500)
            );
        }
        foreach (array_chunk($posts, 100) as $chunk) {
            $this->connection->executeStatement(
                'INSERT INTO posts (user_id, title, body, views_count) VALUES ' . implode(',', $chunk)
            );
        }

        $output->writeln('Done.');

        return Command::SUCCESS;
    }
}
