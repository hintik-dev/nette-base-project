<?php declare(strict_types=1);
require __DIR__ . '/bootstrap.php';

use Tester\Assert;

// Simple smoke test
Assert::true(true);
Assert::same('Hello, World!', 'Hello, World!');
Assert::type('string', 'Hello, World!');
