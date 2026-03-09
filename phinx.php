<?php

declare(strict_types=1);

$neonFile = __DIR__ . '/config/local/database.neon';

if (!file_exists($neonFile)) {
    throw new RuntimeException('Database config not found: ' . $neonFile);
}

$config = \Nette\Neon\Neon::decodeFile($neonFile);
$db = $config['database'];

// Parsování DSN: mysql:host=...;dbname=...
$dsn = $db['dsn'];
preg_match('/host=([^;]+)/', $dsn, $hostMatch);
preg_match('/dbname=([^;]+)/', $dsn, $dbnameMatch);

$host = $hostMatch[1] ?? '127.0.0.1';
$dbname = $dbnameMatch[1] ?? '';

return [
    'paths' => [
        'migrations' => 'db/migrations',
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'default',
        'default' => [
            'adapter' => 'mysql',
            'host' => $host,
            'name' => $dbname,
            'user' => $db['user'],
            'pass' => $db['password'],
            'port' => '3306',
            'charset' => 'utf8mb4',
        ],
    ],
    'version_order' => 'creation',
];
