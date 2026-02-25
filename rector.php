<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector;

return RectorConfig::configure()
	->withPaths([
		__DIR__ . '/app',
		__DIR__ . '/tests',
	])
	->withImportNames(
		importNames: false,
		importDocBlockNames: false,
		importShortClasses: false,
		removeUnusedImports: true,
	)
	->withRules([
		DeclareStrictTypesRector::class,
	]);
