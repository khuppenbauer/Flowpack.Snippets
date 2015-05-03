<?php
namespace Flowpack\Snippets\Command;

/*                                                                           *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".        *
 *                                                                           *
 *                                                                           */

use Flowpack\ElasticSearch\Indexer\Object\ObjectIndexer;
use Flowpack\Snippets\Domain\Repository\PostRepository;
use Flowpack\Snippets\Service\CaptureService;
use TYPO3\Flow\Annotations as Flow;

/**
 * Command controller for ElasticSearch Indexer
 *
 * @Flow\Scope("singleton")
 */
class ElasticsearchCommandController extends \TYPO3\Flow\Cli\CommandController {

	/**
	 * @Flow\Inject
	 * @var PostRepository
	 */
	protected $postRepository;

	/**
	 * @Flow\Inject
	 * @var ObjectIndexer
	 */
	protected $elasticSearchObjectIndexer;

	/**
	 * @Flow\Inject
	 * @var CaptureService
	 */
	protected $captureService;


	/**
	 * Index Posts in Elasticsearch
	 *
	 * @return void
	 */
	public function indexCommand() {
		$posts = $this->postRepository->findAll();
		foreach ($posts as $post) {
			$this->captureService->createCapture($post);
			$this->elasticSearchObjectIndexer->indexObject($post);
		}
	}

}

?>