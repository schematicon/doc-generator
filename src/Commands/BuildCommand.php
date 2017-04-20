<?php

/**
 * This file is part of the Schematicon library.
 * @license    MIT
 * @link       https://github.com/schematicon/doc-generator
 */

namespace Schematicon\DocGenerator\Commands;

use Nette\Neon\Entity;
use Nette\Neon\Neon;
use Schematicon\ApiValidator\Loader;
use Schematicon\ApiValidator\Normalizer;
use Schematicon\DocGenerator\Generator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class BuildCommand extends Command
{
	protected function configure()
	{
		$this->setName('build')
			->setDescription('Builds API documentation into specified directory.')
			->addArgument('schemaIndex', InputArgument::REQUIRED, 'Schema index neon file with API description.')
			->addArgument('outDir', InputArgument::REQUIRED, 'Directory for final documentation build.')
			->addOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Generator configuration neon file.');
	}


	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$schemaIndex = $input->getArgument('schemaIndex');
		$outDir = $input->getArgument('outDir');
		$configFile = $input->getOption('config');

		$loader = new Loader();
		$normalizer = new Normalizer();
		$generator = new Generator();

		$schema = $loader->run($schemaIndex);
		$normalizedSchema = $normalizer->normalize($schema);

		if ($configFile !== null) {
			if (!file_exists($configFile)) {
				throw new \RuntimeException("Configuration file '$configFile' does not exist.");
			}
			$config = $this->loadConfig($configFile);
			$config['templates'] = array_map(function ($templateFile) use ($configFile) {
				return realpath(dirname($configFile) . '/' . $templateFile);
			}, $config['templates']);
		} else {
			$config = [];
		}

		$indexHtml = $generator->generate($normalizedSchema, $config);

		file_put_contents($outDir . '/index.html', $indexHtml);
		copy(__DIR__ . '/../templates/style.css', $outDir . '/style.css');
	}


	protected function loadConfig(string $file)
	{
		$content = file_get_contents($file);
		$decoded = Neon::decode($content);
		array_walk_recursive($decoded, function (& $value) use ($file) {
			if ($value instanceof Entity) {
				if ($value->value === '@include') {
					$value = $this->loadConfig(dirname($file) . '/'. $value->attributes[0]);
				}
			}
		});
		return $decoded;
	}
}
