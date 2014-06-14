<?php
namespace Flowpack\Snippets\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use Elastica\Client;
use Elastica\Query\QueryString;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\Controller\ActionController;
use Flowpack\ElasticSearch\Domain\Factory\ClientFactory;
use Flowpack\Snippets\Domain\Model\Post;
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
	 * @param string $category
	 * @param string $tag
	 * @param string $author
	 * @return void
	 */
	public function searchAction($search = array(), $category = '', $tag = '', $author = '') {
		$elasticaClient = new Client();
		$elasticaIndex = $elasticaClient->getIndex('snippets');
		$elasticaQuery  = new QueryString();
		$elasticaQuery->setDefaultOperator('AND');
		if (!empty($search)) {
			$elasticaQuery->setQuery($search['query']);
		} elseif (!empty($category)) {
			$elasticaQuery->setFields(array('category'));
			$elasticaQuery->setQuery($category);
		} elseif (!empty($tag)) {
			$elasticaQuery->setFields(array('tags'));
			$elasticaQuery->setQuery($tag);
		} elseif (!empty($author)) {
			$elasticaQuery->setFields(array('author'));
			$elasticaQuery->setQuery($author);
		} else {
			$elasticaQuery->setQuery('*');
		}
		$elasticaResults = $elasticaIndex->search($elasticaQuery)->getResults();
		$posts = array();
		foreach ($elasticaResults as $elasticaResult) {
			$data = $elasticaResult->getData();
			$post = $this->postRepository->findByIdentifier($elasticaResult->getId());
			/** @var Post $post */
			if ($post !== NULL) {
				if (!empty($data['url'])) {
					$post->setCode($data['code']);
					$post->setImage($data['image']);
					$post->setType($data['type']);
					$post->setProviderIcon($data['providerIcon']);
					$post->setProviderName($data['providerName']);
					$post->setProviderUrl($data['providerUrl']);
				}
				$posts[] = $post;
			}
		}
		$this->view->assign('posts', $posts);
	}

}