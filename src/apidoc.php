<?php

/**
 * This file is part of the Schematicon library.
 * @license    MIT
 * @link       https://github.com/schematicon/doc-generator
 */

namespace Schematicon\DocGenerator;

use Schematicon\DocGenerator\Commands\BuildCommand;
use Symfony\Component\Console\Application;


foreach ([
	__DIR__ . '/../../../autoload.php', // composer require
	__DIR__ . '/../vendor/autoload.php', // composer create-project
] as $file) {
	if (file_exists($file)) {
		require_once $file;
		break;
	}
}


$application = new Application();
$application->addCommands([
	new BuildCommand(),
]);
$application->run();
