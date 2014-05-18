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
use Goutte\Client as Goutte;
use Flowpack\ElasticSearch\Indexer\Object\Transform\TransformerInterface;

/**
 * @Flow\Scope("singleton")
 */
class UrlCrawlerTransformer implements TransformerInterface {

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
		if(!empty($source)) {
			$client = new Goutte();
			$crawler = $client->request('GET', $source);
			$statusCode = $client->getResponse()->getStatus();
			if ($statusCode === 200) {
				return trim($crawler->filterXPath('html/body')->text());
			}
		}
		return $source;
	}

}

