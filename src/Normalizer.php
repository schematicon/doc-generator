<?php

/**
 * This file is part of the Schematicon library.
 * @license    MIT
 * @link       https://github.com/schematicon/doc-generator
 */

namespace Schematicon\DocGenerator;

use Schematicon\Validator\Normalizer as SchematiconNormalizer;


class Normalizer
{
	public function normalize(array $data): array
	{
		$data = $this->normalizeEndpoints($data);
		$data = $this->normalizeWrappers($data);
		return $data;
	}


	private function normalizeEndpoints(array $data): array
	{
		foreach ($data['sections'] as $i => $section) {
			$endpoints = [];
			foreach ($section['endpoints'] as $index => $value) {
				if (is_int($index)) {
					foreach ($value as $url => $endpoint) {
						$endpoints[$url] = $endpoint;
					}
				} else {
					$endpoints[$index] = $value;
				}
			}
			$data['sections'][$i]['endpoints'] = $endpoints;
		}

		return $data;
	}


	private function normalizeWrappers(array $data): array
	{
		$normalizer = new SchematiconNormalizer();
		foreach ($data['sections'] as $i => $section) {
			foreach ($section['endpoints'] as $url => $generalEndpoint) {
				foreach (['put', 'get', 'post', 'delete', 'patch'] as $httpMethod) {
					if (!isset($generalEndpoint[$httpMethod])) {
						continue;
					}

					$endpoint = $generalEndpoint[$httpMethod];
					if (isset($data['response_ok']['wrapper'])) {
						$schema = $data['response_ok']['wrapper'];
						array_walk_recursive($schema, function (& $value) use ($endpoint) {
							if ($value === '@@') {
								$value = $endpoint['response_ok']['schema'] ?? ['type' => 'null'];
							}
						});
						$data['sections'][$i]['endpoints'][$url][$httpMethod]['response_ok']['schema'] = $normalizer->normalize($schema);
					}
					if (isset($data['response_error']['wrapper'])) {
						$schema = $data['response_error']['wrapper'];
						array_walk_recursive($schema, function (& $value) use ($endpoint) {
							if ($value === '@@') {
								$value = $endpoint['response_error']['schema'] ?? ['type' => 'null'];
							}
						});
						$data['sections'][$i]['endpoints'][$url][$httpMethod]['response_error']['schema'] = $normalizer->normalize($schema);
					}

					$parameters = array_merge_recursive($generalEndpoint['parameters'] ?? [], $endpoint['parameters'] ?? []);
					array_walk($parameters, function (& $value) use ($normalizer) {
						$value = $normalizer->normalize($value);
					});
					$data['sections'][$i]['endpoints'][$url][$httpMethod]['parameters'] = $parameters ?: null;
				}
			}
		}

		return $data;
	}
}
