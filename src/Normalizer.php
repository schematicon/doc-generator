<?php

/**
 * This file is part of the Schematicon library.
 * @license    MIT
 * @link       https://github.com/schematicon/doc-generator
 */

namespace Schematicon\DocGenerator;

use Schematicon\Validator\Normalizer as SchematiconNormalizer;
use Schematicon\Validator\SchemaValidator;


class Normalizer
{
	/** @var SchemaValidator */
	private $validator;

	/** @var SchematiconNormalizer */
	private $normalizer;


	public function __construct()
	{
		$this->validator = new SchemaValidator();
		$this->normalizer = new SchematiconNormalizer();
	}


	public function normalize(array $data): array
	{
		$data = $this->normalizeEndpointLists($data);
		$data = $this->normalizeEndpointSchemas($data);
		$data = $this->normalizeResourceSchemas($data);
		return $data;
	}


	private function normalizeEndpointLists(array $data): array
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


	private function normalizeEndpointSchemas(array $data): array
	{
		foreach ($data['sections'] as $i => $section) {
			foreach ($section['endpoints'] as $url => $generalEndpoint) {
				foreach (['put', 'get', 'post', 'delete', 'patch'] as $httpMethod) {
					if (!isset($generalEndpoint[$httpMethod])) {
						continue;
					}
					$endpoint = $generalEndpoint[$httpMethod];

					// add wrappers
					foreach (['response_ok', 'response_error'] as $endpointPart) {
						if (isset($data[$endpointPart]['wrapper'])) {
							$schema = $data[$endpointPart]['wrapper'];
							array_walk_recursive($schema, function (& $value) use ($endpoint, $endpointPart) {
								if ($value === '@@') {
									$value = $endpoint[$endpointPart]['schema'] ?? ['type' => 'null'];
								}
							});
							$data['sections'][$i]['endpoints'][$url][$httpMethod][$endpointPart]['schema'] = $endpoint[$endpointPart]['schema'] = $schema;
						}
					}

					// normalize & validate schemas
					foreach (['request', 'response_ok', 'response_error'] as $endpointPart) {
						if (!isset($endpoint[$endpointPart]['schema'])) {
							continue;
						}

						$validationResult = $this->validator->validate($endpoint[$endpointPart]['schema']);
						if (!$validationResult->isValid()) {
							throw new \RuntimeException("Schema for $url $httpMethod $endpointPart is not valid. " . implode("\n", $validationResult->getErrors()));
						}
						$data['sections'][$i]['endpoints'][$url][$httpMethod][$endpointPart]['schema'] = $this->normalizer->normalize($endpoint[$endpointPart]['schema']);
					}

					// normalize params
					$parameters = array_merge_recursive($generalEndpoint['parameters'] ?? [], $endpoint['parameters'] ?? []);
					array_walk($parameters, function (& $value) {
						$value = $this->normalizer->normalize($value);
					});
					$data['sections'][$i]['endpoints'][$url][$httpMethod]['parameters'] = $parameters ?: null;
				}
			}
		}

		return $data;
	}


	private function normalizeResourceSchemas($data)
	{
		$schemaValidator = new SchemaValidator();
		$normalizer = new SchematiconNormalizer();

		foreach ($data['resources'] ?? [] as $resourceName => $schema) {
			if (!$schemaValidator->validate($schema)->isValid()) {
				throw new \RuntimeException("Resource $resourceName is not valid schema");
			}
			$data['resources'][$resourceName] = $normalizer->normalize($schema);
		}

		return $data;
	}
}
