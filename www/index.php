<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$isApi = substr($_SERVER['REQUEST_URI'], 0, 4) === '/api';

$bootstrap = new App\Bootstrap;
$container = $bootstrap->bootWebApplication();

$application = $isApi
    ? $container->getByType(\Apitte\Core\Application\IApplication::class)
    : $container->getByType(Nette\Application\Application::class);
$application->run();
