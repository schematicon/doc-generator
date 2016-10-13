<?php

/**
 * This file is part of the Schematicon library.
 * @license    MIT
 * @link       https://github.com/schematicon/doc-generator
 */

namespace Schematicon\DocGenerator\Commands;

use Schematicon\DocGenerator\Generator;
use Schematicon\DocGenerator\Loader;
use Schematicon\DocGenerator\Normalizer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class BuildCommand extends Command
{
	protected function configure()
	{
		$this->setName('build')
			->setDescription('Builds API documentation into specified directory.')
			->addArgument('schemaIndex', InputArgument::REQUIRED, 'Schema index neon file with API description.')
			->addArgument('outDir', InputArgument::REQUIRED, 'Directory for final documentation build.');
	}


	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$schemaIndex = $input->getArgument('schemaIndex');
		$outDir = $input->getArgument('outDir');

		$loader = new Loader();
		$normalizer = new Normalizer();
		$generator = new Generator();

		$schema = $loader->run($schemaIndex);
		$normalizedSchema = $normalizer->normalize($schema);
		$indexHtml = $generator->generate($normalizedSchema);

		file_put_contents($outDir . '/index.html', $indexHtml);
		copy(__DIR__ . '/../templates/style.css', $outDir . '/style.css');
	}
}
