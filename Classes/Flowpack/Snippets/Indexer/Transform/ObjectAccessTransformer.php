<?php
namespace Flowpack\Snippets\Indexer\Transform;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.ElasticSearch".*
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Reflection\ObjectAccess;
use Flowpack\ElasticSearch\Indexer\Object\Transform\TransformerInterface;

/**
 * @Flow\Scope("singleton")
 */
class ObjectAccessTransformer implements TransformerInterface {

	/**
	 * Returns the Elasticsearch type this transform() method returns
	 *
	 * @return string
	 */
	public function getTargetMappingType() {
		return 'string';
	}

	/**
	 * @param mixed $source
	 * @param \Flowpack\ElasticSearch\Annotations\Transform $annotation
	 * @return string
	 */
	public function transformByAnnotation($source, \Flowpack\ElasticSearch\Annotations\Transform $annotation) {
		return ObjectAccess::getPropertyPath($source, $annotation->options['propertyPath']);
	}

}

