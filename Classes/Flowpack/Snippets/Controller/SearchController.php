<?php
namespace Flowpack\Snippets\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\Controller\ActionController;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use Flowpack\Snippets\Service\SearchService;

/**
 * Class SearchController
 *
 * @package Flowpack\Snippets\Controller
 */
class SearchController extends ActionController {

	/**
	 * @Flow\Inject
	 * @var SearchService
	 */
	protected $searchService;

	/**
	 * @var integer
	 */
	protected $currentPage = 1;

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
	 * converts tags from select2 library
	 */
	public function initializeSearchAction() {
		if ($this->request->hasArgument('category')) {
			$search['filter']['category'] = $this->request->getArgument('category');
			$this->request->setArgument('search', $search);
		}
		if ($this->request->hasArgument('tags')) {
			$search['filter']['tags'] = $this->request->getArgument('tags');
			$this->request->setArgument('search', $search);
		}
		if ($this->request->hasArgument('tag')) {
			$search['filter']['tags'] = $this->request->getArgument('tag');
			$this->request->setArgument('search', $search);
		}
	}

	/**
	 * @param array $search
	 * @return void
	 */
	public function searchAction($search = array()) {
		$query = !empty($search['query']) ? $search['query'] : '*';
		$filter = isset($search['filter']) ? $search['filter'] : array();
		if (!empty($search['currentPage'])) {
			$this->currentPage = (integer)$search['currentPage'];
		}
		$offset = $this->searchService->calculateOffset($this->currentPage);
		$resultSet = $this->searchService->search($query, $filter, $offset);
		$aggregations = $this->searchService->transformAggregations($resultSet->getAggregations());
		$posts = $this->searchService->transformResult($resultSet->getResults());
		$last = $offset + count($posts);
		$pagination = $this->searchService->buildPagination($this->currentPage, $resultSet->getTotalHits());
		$this->view->assign('totalHits', $resultSet->getTotalHits());
		$this->view->assign('first', $offset + 1);
		$this->view->assign('last', $last);
		$this->view->assign('search', $search);
		$this->view->assign('posts', $posts);
		$this->view->assign('aggregations', $aggregations);
		$this->view->assign('filter', $filter);
		$this->view->assign('pagination', $pagination);
	}

	/**
	 * @return void
	 */
	public function teaserAction() {
		/** @var NodeInterface $node */
		$node = $this->request->getInternalArgument('__node');
		$properties = $node->getProperties();
		if (!empty($properties)) {
			$sortField = !empty($properties['sortField']) ? $properties['sortField'] : $this->settings['teaser']['defaultSortField'];
			$order = !empty($properties['order']) ? $properties['order'] : $this->settings['teaser']['defaultOrder'];
			$size = !empty($properties['size']) ? intval($properties['size']) : $this->settings['teaser']['defaultSize'];
			switch ($properties['type']) {
				case 'list':
					$filter['type'] = 'post';
					$resultSet = $this->searchService->teaserSearch($sortField, $order, $size, $filter);
					$posts = $this->searchService->transformResult($resultSet->getResults());
					break;
				case 'tagcloud':
					$uriBuilder = $this->controllerContext->getUriBuilder();
					$resultSet = $this->searchService->search('*', array(), 0, NULL, $size);
					$tags = $resultSet->getAggregation('tags');
					if (!empty($tags['buckets'])) {
						foreach ($tags['buckets'] as $tag) {
							$uri = $uriBuilder->uriFor('search', array('tag' => $tag['key']), 'Search');
							$word['text'] = $tag['key'];
							$word['weight'] = $tag['doc_count'];
							$word['link']['href'] = $uri;
							$words[] = $word;
						}
						$this->view->assign('words', json_encode($words));
					}
					break;
				case 'favorites':
					$user = $this->securityContext->getPartyByType('Flowpack\Snippets\Domain\Model\User');
					/** @var  $posts \Flowpack\Snippets\Domain\Model\User $user */
					if ($user !== NULL) {
						$posts = $user->getFavorites();
						/** @var \Doctrine\Common\Collections\ArrayCollection $posts */
						$posts = $posts->slice(0, 1);
					}
					break;
			}
			if (!empty($posts)) {
				$this->view->assign('posts', $posts);
			}
			if (isset($properties['title'])) {
				$this->view->assign('title', $properties['title']);
			}
		}
	}

}