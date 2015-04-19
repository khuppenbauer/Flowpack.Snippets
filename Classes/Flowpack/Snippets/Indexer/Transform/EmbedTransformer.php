<?php
namespace Flowpack\Snippets\Indexer\Transform;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use Embed\Embed;
use Embed\Request;
use Goutte\Client as Goutte;
use GuzzleHttp\Client as Guzzle;
use Smalot\PdfParser\Parser;
use TYPO3\Flow\Annotations as Flow;
use Flowpack\ElasticSearch\Indexer\Object\Transform\TransformerInterface;
use TYPO3\Flow\Utility\Arrays;

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
		return 'string';
	}

	/**
	 * @param array $settings
	 */
	public function injectSettings(array $settings) {
		$this->elasticSearch = $settings['elasticSearch'];
		$this->provider = $settings['embed']['provider'];
	}

	/**
	 * @param mixed $source
	 * @param \Flowpack\ElasticSearch\Annotations\Transform $annotation
	 * @return array
	 */
	public function transformByAnnotation($source, \Flowpack\ElasticSearch\Annotations\Transform $annotation) {
		$data = array();
		if (empty($source)) {
			$data['type'] = $this->elasticSearch['type'];
			$data['providerName'] = $this->elasticSearch['index'];
		} else {
			$embed = Embed::create($source);
			if ($embed === FALSE) {
				return;
			}
			$content = $this->getContent($source);
			$data['content'] = $content;
			$data['url'] = $source;
			$data['type'] = $embed->getType();
			$data['image'] = $embed->getImage();
			$data['code'] = $embed->getCode();
			$data['providerName'] = str_replace(' ', '', $embed->getProviderName());
			$data['providerUrl'] = $embed->getProviderUrl();
			$data['providerIcon'] = $embed->getProviderIcon();
			$data['_embed_title'] = $embed->getTitle();
			$data['_embed_description'] = $embed->getDescription();
			$data['_embed_authorName'] = $embed->getAuthorName();
			$data['_embed_authorUrl'] = $embed->getAuthorUrl();

			foreach($embed->getAllProviders() as $key => $value) {
				if (isset($this->provider[$key]) && $this->provider[$key] === TRUE) {
					$params = $value->get();
					foreach ($params as $k => $v) {
						$data['_' . $key][$k] = $v;
					}
				}
			}
		}
		if (empty($data['image'])) {
			$openGraphImages = Arrays::getValueByPath($data, '_OpenGraph.image');
			if (!empty($openGraphImages)) {
				$data['image'] = $openGraphImages[0];
			}
		}
		if (empty($data['_embed_authorName'])) {
			$data['_embed_authorName'] = Arrays::getValueByPath($data, '_OEmbed.author-name');
		}
		if (empty($data['_embed_authorUrl'])) {
			$data['_embed_authorUrl'] = Arrays::getValueByPath($data, '_OEmbed.author-url');
		}
		return $data;
	}

	/**
	 * @param string $source
	 * @return string
	 */
	public function getContent($source) {
		$client = new Goutte();
		$guzzle = new Guzzle();
		$guzzle->setDefaultOption('verify', FALSE);
		$client->setClient($guzzle);
		$crawler = $client->request('GET', $source);
		$response = $client->getResponse();
		$statusCode = $response->getStatus();
		if ($statusCode === 200) {
			$html = $crawler->filterXPath('html/body');
			if (count($html) > 0) {
				$text = trim($crawler->filterXPath('html/body')->text());
				$link = $crawler->selectLink('Download PDF');
				if ($link->getNode(0) !== NULL) {
					$parser = new Parser();
					$pdf = $parser->parseFile($link->link()->getUri());
					$text = $text . ' ' . $pdf->getText();
				}
				return $text;
			}
		}
		if (is_array(json_decode($response->getContent(), TRUE))) {
			return json_decode($response->getContent(), TRUE);
		}
	}

}

