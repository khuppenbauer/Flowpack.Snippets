<?php
namespace Flowpack\Snippets\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\Controller\ActionController;
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
	 * @param array $search
	 * @return void
	 */
	public function searchAction($search = array()) {
		$query = !empty($search['query']) ? $search['query'] : '*';
		$filter = isset($search['filter']) ? $search['filter'] : array();
		$this->search($query, $filter, $search);
	}

	/**
	 * @param string $query
	 * @param array $filter
	 * @param array $search
	 */
	protected function search($query, $filter = array(), $search = array()) {
		$resultSet = $this->searchService->search($query, $filter);
		$facets = $this->searchService->transformFacets($resultSet->getFacets());
		$posts = $this->searchService->transformResult($resultSet->getResults());
		$this->view->assign('totalHits', $resultSet->getTotalHits());
		$this->view->assign('search', $search);
		$this->view->assign('posts', $posts);
		$this->view->assign('facets', $facets);
		$this->view->assign('filter', $filter);
	}

}