<?php
namespace Flowpack\Snippets\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * Provides CLI features for mapping handling
 *
 * @Flow\Scope("singleton")
 */
class MappingCommandController extends \TYPO3\Flow\Cli\CommandController {

	/**
	 * @Flow\Inject
	 * @var \Flowpack\ElasticSearch\Mapping\EntityMappingBuilder
	 */
	protected $entityMappingBuilder;

	/**
	 * @Flow\Inject
	 * @var \Flowpack\ElasticSearch\Domain\Factory\ClientFactory
	 */
	protected $clientFactory;

	/**
	 * @var string
	 */
	protected $settings;

	/**
	 * @param array $settings
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings['elasticSearch'];
	}

	/**
	 * Add mapping from settings
	 *
	 * @return void
	 */
	public function createDefaultMappingCommand() {
		$client = $this->clientFactory->create(NULL);

		$entityMappingCollection = $this->entityMappingBuilder->buildMappingInformation();

		/** @var $mapping \Flowpack\ElasticSearch\Domain\Model\Mapping */
		foreach ($entityMappingCollection AS $mapping) {
			$index = $mapping->getType()->getIndex();
			$index->setClient($client);
			$type = $mapping->getType()->getName();
			$content[$type] = $this->settings['mapping']['fields'];
			$response = $mapping->getType()->request('PUT', '/_mapping', array(), json_encode($content));
			if ($response->getStatusCode() === 200) {
				$this->outputFormatted('<b>OK</b>');
			} else {
				$this->outputFormatted('<b>NOT OK</b>, response code was %d, response body was: %s', array($response->getStatusCode(), $response->getOriginalResponse()->getContent()), 4);
			}
		}
	}
}

