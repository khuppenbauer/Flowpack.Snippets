<?php
namespace Flowpack\Snippets\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\Controller\ActionController;
use TYPO3\Flow\Security\Context;
use TYPO3\Flow\Utility\Arrays;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use Flowpack\Snippets\Service\SearchService;
use Flowpack\Snippets\Service\UserService;

/**
 * Class SearchController
 *
 * @package Flowpack\Snippets\Controller
 */
class SearchController extends ActionController {

	/**
	 * @var Context
	 */
	protected $securityContext;

	/**
	 * @Flow\Inject
	 * @var SearchService
	 */
	protected $searchService;

	/**
	 * @Flow\Inject
	 * @var UserService
	 */
	protected $userService;

	/**
	 * @var integer
	 */
	protected $currentPage = 1;

	/**
	 * @var string
	 */
	protected $settings;

	/**
	 * Injects the Security Context
	 *
	 * @param Context $securityContext
	 * @return void
	 */
	public function injectSecurityContext(Context $securityContext) {
		$this->securityContext = $securityContext;
	}

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
		}
		if ($this->request->hasArgument('tags')) {
			$search['filter']['tags'] = $this->request->getArgument('tags');
		}
		if ($this->request->hasArgument('tag')) {
			$search['filter']['tags'] = $this->request->getArgument('tag');
		}
		if (!empty($search)) {
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
		$sortField = !empty($search['sortField']) ? $search['sortField'] : $this->settings['defaultSortField'];
		$postType = Arrays::getValueByPath($filter, 'postType');
		$aggregationSize = $this->settings['aggregationSize'];
		$resultSet = $this->searchService->fulltextSearch($query, $filter, $offset, $sortField, $aggregationSize);
		$aggregations = $this->searchService->transformAggregations($resultSet->getAggregations(), 'filter');
		$types = $this->searchService->transformAggregations($resultSet->getAggregations(), 'tab');
		$posts = $this->searchService->transformResult($resultSet->getResults());
		$last = $offset + count($posts);
		$pagination = $this->searchService->buildPagination($this->currentPage, $resultSet->getTotalHits());
		$user = $this->userService->getUser();

		$this->view->assign('totalHits', $resultSet->getTotalHits());
		$this->view->assign('first', $offset + 1);
		$this->view->assign('last', $last);
		$this->view->assign('search', $search);
		$this->view->assign('posts', $posts);
		$this->view->assign('aggregations', $aggregations);
		$this->view->assign('types', $types);
		$this->view->assign('filter', $filter);
		$this->view->assign('pagination', $pagination);
		$this->view->assign('sortings', $this->settings['sortings']);
		$this->view->assign('sortField', $sortField);
		$this->view->assign('postType', $postType);
		$this->view->assign('user', $user);
		$this->view->assign('embed', FALSE);
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
			$pluginArguments = $this->request->getParentRequest()->getPluginArguments();
			$post = Arrays::getValueByPath($pluginArguments, 'flowpack_snippets-search.post');
			switch ($properties['type']) {
				case 'list':
					$filter = isset($this->settings['teaser']['filter']) ? $this->settings['teaser']['filter'] : array();
					$resultSet = $this->searchService->teaserSearch($sortField, $order, $size, $filter);
					$posts = $this->searchService->transformResult($resultSet->getResults());
					break;
				case 'tagcloud':
					$uriBuilder = $this->controllerContext->getUriBuilder();
					$resultSet = $this->searchService->tagSearch($size);
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
					$user = $this->userService->getUser();
					/** @var \Flowpack\Snippets\Domain\Model\User $user */
					if ($user !== NULL) {
						$posts = $user->getFavorites();
						/** @var \Doctrine\Common\Collections\ArrayCollection $posts */
						$posts = $posts->slice(0, $size);
					}
					break;
				case 'related':
					if ($post !== NULL) {
						$resultSet = $this->searchService->moreLikeThisSearch($post['__identity'], $size);
						$posts = $this->searchService->transformResultFromRawRequest($resultSet->getData());
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