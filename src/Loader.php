<?php

/**
 * This file is part of the Schematicon library.
 * @license    MIT
 * @link       https://github.com/schematicon/doc-generator
 */

namespace Schematicon\DocGenerator;

use Nette\Neon\Entity;
use Nette\Neon\Neon;


class Loader
{
	public function run($file)
	{
		$content = file_get_contents($file);
		$decoded = Neon::decode($content);
		array_walk_recursive($decoded, function (& $value) use ($file) {
			if ($value instanceof Entity) {
				if ($value->value === '@include') {
					$value = $this->run(dirname($file) . '/'. $value->attributes[0]);
				}
			}
		});
		return $decoded;
	}
}
