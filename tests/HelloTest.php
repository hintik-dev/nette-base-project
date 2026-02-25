<?php declare(strict_types=1);
namespace Tests;

require __DIR__ . '/bootstrap.php';

use Tester\Assert;
use Tester\TestCase;

class HelloTest extends TestCase
{
    public function testTrue(): void
    {
        Assert::true(true);
    }

    public function testSameString(): void
    {
        Assert::same('Hello, World!', 'Hello, World!');
    }

    public function testStringType(): void
    {
        Assert::type('string', 'Hello, World!');
    }
}

(new HelloTest())->run();
