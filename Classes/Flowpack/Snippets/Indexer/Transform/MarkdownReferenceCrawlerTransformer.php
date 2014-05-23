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
class MarkdownReferenceCrawlerTransformer implements TransformerInterface {

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
		return $source;
		try {
			$parsedown = new \Parsedown();
			$text = $parsedown->text($source);
			$references = $parsedown->getReferences();
			if (!empty($references)) {
				$client = new Goutte();
				$sourceArray = array(strip_tags($source));
				foreach ($references as $reference) {
					$url  = $reference['url'];
					$crawler = $client->request('GET', $url);
					$statusCode = $client->getResponse()->getStatus();
					if ($statusCode === 200) {
						$sourceArray[] = trim($crawler->filterXPath('html/body')->text());
					}
				}
				$source = implode(' ', $sourceArray);
			}
			return $source;
		} catch (\Exception $e) {
			return $source;
		}
	}

}

