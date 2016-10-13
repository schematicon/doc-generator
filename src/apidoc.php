<?php

/**
 * This file is part of the Schematicon library.
 * @license    MIT
 * @link       https://github.com/schematicon/doc-generator
 */

namespace Schematicon\DocGenerator;

use Schematicon\DocGenerator\Commands\BuildCommand;
use Symfony\Component\Console\Application;


require_once __DIR__ . '/../vendor/autoload.php';


$application = new Application();
$application->addCommands([
	new BuildCommand(),
]);
$application->run();
