<?php
namespace Flowpack\Snippets\Indexer\Transform;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Goutte\Client as Goutte;
use GuzzleHttp\Client as Guzzle;
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
	 * @param array $settings
	 */
	public function injectSettings(array $settings) {
		$this->elasticSearch = $settings['elasticSearch'];
	}

	/**
	 * @param mixed $source
	 * @param \Flowpack\ElasticSearch\Annotations\Transform $annotation
	 * @return string
	 */
	public function transformByAnnotation($source, \Flowpack\ElasticSearch\Annotations\Transform $annotation) {
		$data = array();
		$data['type'] = $this->elasticSearch['type'];
		$data['providerName'] = $this->elasticSearch['index'];
		if (!empty($source)) {
			try {
				$parsedown = new \Parsedown();
				$parsedown->text($source);
				$references = $parsedown->getReferences();
				if (!empty($references)) {
					$client = new Goutte();
					$guzzle = new Guzzle();
					$guzzle->setDefaultOption('verify', FALSE);
					$client->setClient($guzzle);
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
				$data['content'] = $source;
			} catch (\Exception $e) {
				$data['content'] = $source;
			}
		}
		return $data;
	}

}

