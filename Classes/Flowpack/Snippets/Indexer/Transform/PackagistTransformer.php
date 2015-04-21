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
use TYPO3\Flow\Utility\Arrays;

/**
 * @Flow\Scope("singleton")
 */
class PackagistTransformer implements TransformerInterface {

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
		if (!empty($source)) {
			try {
				$uri = 'https://packagist.org/p/' . $source . '.json';
				$client = new Goutte();
				$guzzle = new Guzzle();
				$guzzle->setDefaultOption('verify', FALSE);
				$client->setClient($guzzle);
				$client->request('GET', $uri);
				$response = $client->getResponse();
				$statusCode = $response->getStatus();
				if ($statusCode === 200) {
					$content = json_decode($response->getContent(), TRUE);
					$content = Arrays::getValueByPath($content, 'packages.' . $source . '.dev-master');
					$data['_packagist'] = $content;
					return $data;
				}
			} catch (\Exception $e) {
			}
		}
	}

}

