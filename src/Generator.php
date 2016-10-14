<?php

/**
 * This file is part of the Schematicon library.
 * @license    MIT
 * @link       https://github.com/schematicon/doc-generator
 */

namespace Schematicon\DocGenerator;

use Latte\Engine;
use Latte\Runtime\Html;


class Generator
{
	public function generate($api)
	{
		$latte = new Engine();
		$latte->setTempDirectory(__DIR__ . '/../temp');
		$latte->addFilter('urlvars', function ($url) {
			return new Html(preg_replace('#(\{[\w_]+\})#', '<span class="url-var">\1</span>', $url));
		});
		return $latte->renderToString(__DIR__ . '/templates/content.latte', ['api' => $api]);
	}
}
