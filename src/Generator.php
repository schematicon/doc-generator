<?php

/**
 * This file is part of the Schematicon library.
 * @license    MIT
 * @link       https://github.com/schematicon/doc-generator
 */

namespace Schematicon\DocGenerator;

use Latte\Engine;


class Generator
{
	public function generate($api)
	{
		$latte = new Engine();
		$latte->setTempDirectory(__DIR__ . '/../temp');
		return $latte->renderToString(__DIR__ . '/templates/content.latte', ['api' => $api]);
	}
}
