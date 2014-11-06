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
		$sortField = isset($properties['sortField']) ? $properties['sortField'] : $this->settings['teaser']['defaultSortField'];
		$order = isset($properties['order']) ? $properties['order'] : $this->settings['teaser']['defaultOrder'];
		$size = isset($properties['size']) ? $properties['size'] : $this->settings['teaser']['defaultSize'];
		$resultSet = $this->searchService->teaserSearch($sortField, $order, $size);
		$posts = $this->searchService->transformResult($resultSet->getResults());
		$this->view->assign('posts', $posts);
		if (isset($properties['title'])) {
			$this->view->assign('title', $properties['title']);
		}
	}

}