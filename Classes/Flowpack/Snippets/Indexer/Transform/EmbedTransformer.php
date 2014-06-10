<?php
namespace Flowpack\Snippets\Indexer\Transform;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use Embed\Embed;
use Embed\Request;
use Goutte\Client as Goutte;
use TYPO3\Flow\Annotations as Flow;
use Flowpack\ElasticSearch\Indexer\Object\Transform\TransformerInterface;

/**
 * @Flow\Scope("singleton")
 */
class EmbedTransformer implements TransformerInterface {

	/**
	 * Returns the Elasticsearch type this transform() method returns
	 *
	 * @return string
	 */
	public function getTargetMappingType() {
		return 'array';
	}

	/**
	 * @param array $settings
	 */
	public function injectSettings(array $settings) {
		$this->provider = $settings['embed']['provider'];
	}

	/**
	 * @param mixed $source
	 * @param \Flowpack\ElasticSearch\Annotations\Transform $annotation
	 * @return array
	 */
	public function transformByAnnotation($source, \Flowpack\ElasticSearch\Annotations\Transform $annotation) {
		$embed = Embed::create($source);

		$data = array();
		$data['content'] = $this->getContent($source);
		$data['url'] = $source;
		$data['type'] = $embed->getType();
		$data['image'] = $embed->getImage();
		$data['code'] = $embed->getCode();
		$data['providerName'] = $embed->getProviderName();
		$data['providerUrl'] = $embed->getProviderUrl();
		$data['providerIcon'] = $embed->getProviderIcon();
		$data['_embed_title'] = $embed->getTitle();
		$data['_embed_description'] = $embed->getDescription();
		$data['_embed_authorName'] = $embed->getAuthorName();
		$data['_embed_authorUrl'] = $embed->getAuthorUrl();

		foreach($embed->providers as $key => $value) {
			if (isset($this->provider[$key]) && $this->provider[$key] === TRUE) {
				$params = $value->get();
				foreach ($params as $k => $v) {
					$data['_' . $key][$k] = $v;
				}
			}
		}
		return $data;
	}

	/**
	 * @param string $source
	 * @return string
	 */
	public function getContent($source) {
		$client = new Goutte();
		$crawler = $client->request('GET', $source);
		$statusCode = $client->getResponse()->getStatus();
		if ($statusCode === 200) {
			return trim($crawler->filterXPath('html/body')->text());
		}
	}

}

