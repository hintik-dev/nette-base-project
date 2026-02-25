<?php

declare(strict_types=1);

return [
	'paths' => [
		'migrations' => 'db/migrations',
	],
	'environments' => [
		'default_migration_table' => 'phinxlog',
		'default_environment' => 'default',
		'default' => [
			'adapter' => 'mysql',
			'host' => getenv('DB_HOST') ?: 'database',
			'name' => getenv('DB_NAME') ?: 'database',
			'user' => getenv('DB_USER') ?: 'root',
			'pass' => getenv('DB_PASS') ?: 'root_pristup_123',
			'port' => getenv('DB_PORT') ?: '3306',
			'charset' => 'utf8mb4',
		],
	],
	'version_order' => 'creation',
];
