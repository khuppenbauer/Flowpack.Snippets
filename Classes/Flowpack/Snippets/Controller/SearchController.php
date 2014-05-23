<?php
namespace Flowpack\Snippets\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\Controller\ActionController;
use Flowpack\ElasticSearch\Domain\Factory\ClientFactory;
use Flowpack\Snippets\Domain\Model\PostType;
use Flowpack\Snippets\Domain\Repository\PostRepository;

/**
 * Class SearchController
 *
 * @package Flowpack\Snippets\Controller
 */
class SearchController extends ActionController {

	/**
	 * @Flow\Inject
	 * @var ClientFactory
	 */
	protected $clientFactory;

	/**
	 * @Flow\Inject
	 * @var PostRepository
	 */
	protected $postRepository;

	/**
	 * @param array $search
	 * @return void
	 */
	public function searchAction($search = array()) {
		$posts = array();
		$client = $this->clientFactory->create();
		$snippetsIndex = $client->findIndex('snippets');
		$postType = new PostType($snippetsIndex, 'post');
		if (empty($search)) {
			$query['query']['matchAll'] = array();
			$query['query']['facets'] = array();
		} else {
			$query['query']['match']['_all'] = $search;
			$query['query']['match']['_all']['operator'] = 'or';
		}
		$response = $postType->search($query);
		$hits = $response->getTreatedContent()['hits'];
		foreach ($hits['hits'] as $hit) {
			$posts[] = $this->postRepository->findByIdentifier($hit['_id']);
		}
		$this->view->assign('posts', $posts);
	}

}