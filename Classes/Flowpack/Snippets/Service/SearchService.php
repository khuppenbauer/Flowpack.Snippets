<?php
namespace Flowpack\Snippets\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use Flowpack\Snippets\Aggregation\TopHits;
use Flowpack\Snippets\Domain\Model\User;
use TYPO3\Flow\Annotations as Flow;
use Elastica\Aggregation;
use Elastica\Filter;
use Elastica\Request;
use Elastica\Client;
use Elastica\Query;
use Elastica\Query\QueryString;
use Elastica\Query\Filtered;
use Elastica\ResultSet;
use Flowpack\Snippets\Domain\Repository\PostRepository;
use Flowpack\Snippets\Domain\Model\Post;
use TYPO3\Flow\Utility\Arrays;

/**
 * Class SearchService
 *
 * @package Flowpack\Snippets\Service
 */
class SearchService {

	/**
	 * The settings
	 *
	 * @var string
	 */
	protected $settings;

	/**
	 * @Flow\Inject
	 * @var UserService
	 */
	protected $userService;

	/**
	 * @Flow\Inject
	 * @var PostRepository
	 */
	protected $postRepository;

	/**
	 * @var integer
	 */
	protected $displayRangeStart;

	/**
	 * @var integer
	 */
	protected $displayRangeEnd;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * @param array $settings
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings['elasticSearch'];
	}

	/**
	 * @param string $query
	 * @param array $filter
	 * @param integer $offset
	 * @param string $sortField
	 * @param integer $aggregationSize
	 * @return ResultSet
	 */
	public function fulltextSearch($query = '*', $filter = array(), $offset = 0, $sortField, $aggregationSize) {
		$elasticaClient = $this->createClient();
		$elasticaIndex = $elasticaClient->getIndex($this->settings['index']);
		$elasticaType = $elasticaIndex->getType($this->settings['type']);

		$elasticaQuery = new Query();
		$elasticaQuery->setFrom($offset);
		$elasticaQuery->setSize($this->settings['hitsPerPage']);

		// querystring
		$elasticaQueryString  = new QueryString();
		$elasticaQueryString->setQuery($query);
		$elasticaQuery->setQuery($elasticaQueryString);

		// add aggregations
		$aggregations = call_user_func_array('array_merge', $this->settings['aggregations']);
		foreach ($aggregations as $aggregation) {
			$aggTerm = new Aggregation\Terms($aggregation);
			$aggTerm->setField($aggregation . '.raw');
			$aggTerm->setSize($aggregationSize);

			$aggFilter = new Aggregation\Filter($aggregation);
			$elasticaFilterBool = new Filter\Bool();
			foreach ($filter as $filterName => $filterValue) {
				if (!empty($filterValue) && $filterName !== $aggregation) {
					if (is_string($filterValue)) {
						$filterValue = array($filterValue);
					}
					$elasticaFilter = new Filter\Terms();
					$elasticaFilter->setTerms($filterName, $filterValue);
					$elasticaFilterBool->addMust($elasticaFilter);
				}
			}
			$filterArray = $elasticaFilterBool->toArray();
			if (!empty($filterArray)) {
				$aggFilter->setFilter($elasticaFilterBool);
				$aggFilter->addAggregation($aggTerm);
				$elasticaQuery->addAggregation($aggFilter);
			} else {
				$elasticaQuery->addAggregation($aggTerm);
			}
		}

		// add postfilter
		$elasticaFilterBool = new Filter\Bool();
		foreach ($filter as $filterName => $filterValue) {
			if (!empty($filterValue)) {
				if (is_string($filterValue)) {
					$filterValue = array($filterValue);
				}
				$elasticaFilter = new Filter\Terms();
				$elasticaFilter->setTerms($filterName, $filterValue);
				$elasticaFilterBool->addMust($elasticaFilter);
			}
		}
		$elasticaQuery->setPostFilter($elasticaFilterBool);

		//sorting
		if (!empty($sortField)) {
			$elasticaQuery->setSort(array($sortField => array('order' => 'desc')));
		}

		// search
		$resultSet = $elasticaType->search($elasticaQuery);
		return $resultSet;
	}

	/**
	 * @param string $sortField
	 * @param string $order
	 * @param integer $size
	 * @param array $filter
	 * @return ResultSet
	 */
	public function teaserSearch($sortField, $order, $size, $filter = array()) {
		$elasticaClient = $this->createClient();
		$elasticaIndex = $elasticaClient->getIndex($this->settings['index']);
		$elasticaType = $elasticaIndex->getType($this->settings['type']);

		$elasticaQuery = new Query();
		$elasticaQuery->setSort(array($sortField => array('order' => $order)));
		$elasticaQuery->setSize($size);

		// add filter
		$elasticaFilterAnd = new Filter\Bool();
		foreach ($filter as $filterName => $filterValue) {
			if (!empty($filterValue)) {
				$elasticaFilter = new Filter\Term();
				$elasticaFilter->setTerm($filterName, $filterValue);
				$elasticaFilterAnd->addMust($elasticaFilter);
			}
		}
		$elasticaQueryFilter = new Filtered(NULL, $elasticaFilterAnd);
		$elasticaQuery->setQuery($elasticaQueryFilter);

		// search
		$resultSet = $elasticaType->search($elasticaQuery);
		return $resultSet;
	}

	/**
	 * @param integer $size
	 * @return array
	 */
	public function tagSearch($size) {
		$elasticaClient = $this->createClient();
		$elasticaIndex = $elasticaClient->getIndex($this->settings['index']);
		$elasticaType = $elasticaIndex->getType($this->settings['type']);

		$elasticaQuery = new Query();
		$elasticaQuery->setSize(0);

		// add aggregations
		$termsAgg = new Aggregation\Terms('tags');
		$termsAgg->setField('tags.raw');
		$termsAgg->setSize($size);
		$elasticaQuery->addAggregation($termsAgg);

		// search
		$resultSet = $elasticaType->search($elasticaQuery);
		return $resultSet;
	}

	/**
	 * @param string $id
	 * @return array
	 */
	public function idSearch($id) {
		$elasticaClient = $this->createClient();
		$elasticaIndex = $elasticaClient->getIndex($this->settings['index']);
		$elasticaType = $elasticaIndex->getType($this->settings['type']);
		$result = $elasticaType->getDocument($id);
		return $result->getData();
	}

	/**
	 * @param string $id
	 * @param integer $size
	 * @return array
	 */
	public function moreLikeThisSearch($id, $size) {
		$elasticaClient = $this->createClient();
		$elasticaIndex = $elasticaClient->getIndex($this->settings['index']);
		$elasticaType = $elasticaIndex->getType($this->settings['type']);

		$settings = $this->settings['moreLikeThis'];
		if (isset($settings['params'])) {
			$query['query']['bool']['must'][0]['more_like_this'] = $settings['params'];
		}
		if (isset($settings['fields'])) {
			$query['query']['bool']['must'][0]['more_like_this']['fields'] = $settings['fields'];
		}
		$query['query']['bool']['must'][0]['more_like_this']['docs'][]['_id'] = $id;
		if (isset($settings['term'])) {
			$query['query']['bool']['must'][1]['term'] = $settings['term'];
		}
		$query['size'] = $size;

		$path = $elasticaIndex->getName() . '/' . $elasticaType->getName() . '/_search';
		$result = $elasticaClient->request($path, Request::POST, $query);
		return $result;
	}

	/**
	 * @param User $user
	 * @param string $state
	 * @return ResultSet
	 */
	public function notificationCountSearch(User $user, $state) {
		$elasticaClient = $this->createClient();
		$elasticaIndex = $elasticaClient->getIndex($this->settings['index']);
		$elasticaType = $elasticaIndex->getType('notification');

		$elasticaQuery = $this->generateNotificationBaseQuery($user, $state);

		// add aggregations
		$termsAgg = new Aggregation\Terms('type');
		$termsAgg->setField('type');
		$elasticaQuery->addAggregation($termsAgg);

		// search
		$resultSet = $elasticaType->search($elasticaQuery);
		return $resultSet;
	}

	/**
	 * @param User $user
	 * @param string $state
	 * @return ResultSet
	 */
	public function notificationListSearch(User $user, $state) {
		$elasticaClient = $this->createClient();
		$elasticaIndex = $elasticaClient->getIndex($this->settings['index']);
		$elasticaType = $elasticaIndex->getType('notification');

		$elasticaQuery = $this->generateNotificationBaseQuery($user, $state);

		// add aggregations
		$termsAgg = new Aggregation\Terms('type');
		$termsAgg->setField('type');
		$termsAgg->setOrder('timestamp', 'desc');

		$hitsAgg = new TopHits('hits');
		$hitsAgg->setSource(array('include' => array('target', 'source', 'timestamp', 'user', 'post', 'state')));

		$timestampAgg = new Aggregation\Max('timestamp');
		$timestampAgg->setField('timestamp');

		$termsAgg->addAggregation($hitsAgg);
		$termsAgg->addAggregation($timestampAgg);

		$elasticaQuery->addAggregation($termsAgg);

		// search
		$resultSet = $elasticaType->search($elasticaQuery);
		return $resultSet;
	}

	/**
	 * @param User $user
	 * @param $state
	 * @return Query
	 */
	public function generateNotificationBaseQuery(User $user, $state) {
		$elasticaQuery = new Query();
		$elasticaQuery->setSize(0);

		// add filter
		$elasticaFilterAnd = new Filter\Bool();

		$elasticaFilter = new Filter\Term();
		$elasticaFilter->setTerm('target', (string)$user);
		$elasticaFilterAnd->addMust($elasticaFilter);

		$elasticaFilter = new Filter\Term();
		$elasticaFilter->setTerm('state', $state);
		$elasticaFilterAnd->addMust($elasticaFilter);

		$elasticaQueryFilter = new Filtered(NULL, $elasticaFilterAnd);
		$elasticaQuery->setQuery($elasticaQueryFilter);
		return $elasticaQuery;
	}

	/**
	 */
	public function followingSearch() {
		$elasticaClient = $this->createClient();
		$elasticaIndex = $elasticaClient->getIndex($this->settings['index']);
		$elasticaType = $elasticaIndex->getType($this->settings['type']);

		/** @var User $user */
		$user = $this->userService->getUser();
		$elasticaQuery = $this->generateFollowingQuery($user);

		// search
		$resultSet = $elasticaType->search($elasticaQuery);
		return $resultSet;
	}

	/**
	 * @param User $user
	 * @return Query
	 */
	public function generateFollowingQuery(User $user) {
		$elasticaQuery = new Query();

		$followedCategories = $user->getFollowedCategories();
		$followedTags = $user->getFollowedTags();
		$followedAuthors = $user->getFollowedUsers();

		$elasticaFilterAnd = new Filter\Bool();
		if ($followedCategories->count() > 0) {
			foreach ($followedCategories as $category) {
				$categories[] = strtolower((string)$category);
			}
			$elasticaFilter = new Filter\Terms('category', $categories);
			$elasticaFilterAnd->addShould($elasticaFilter);
		}

		if ($followedTags->count() > 0) {
			foreach ($followedTags as $tag) {
				$tags[] = strtolower((string)$tag);
			}
			$elasticaFilter = new Filter\Terms('tags', $tags);
			$elasticaFilterAnd->addShould($elasticaFilter);
		}

		if ($followedAuthors->count() > 0) {
			foreach ($followedAuthors as $author) {
				$authors[] = strtolower((string)$author);
			}
			$elasticaFilter = new Filter\Terms('author', $authors);
			$elasticaFilterAnd->addShould($elasticaFilter);
		}

		$elasticaQueryFilter = new Filtered(NULL, $elasticaFilterAnd);
		$elasticaQuery->setQuery($elasticaQueryFilter);
		return $elasticaQuery;
	}

	/**
	 * @param $results
	 * @return array
	 */
	public function transformResult($results) {
		$posts = array();
		foreach ($results as $result) {
			$data = $result->getData();
			$post = $this->postRepository->findByIdentifier($result->getId());
			/** @var Post $post */
			if ($post !== NULL) {
				if (!empty($data['url'])) {
					$post->setCode($data['code']);
					$post->setImage($data['image']);
					$post->setType($data['type']);
					$post->setProviderIcon($data['providerIcon']);
					$post->setProviderName($data['providerName']);
					$post->setProviderUrl($data['providerUrl']);
					$post->setAuthorName($data['_embed_authorName']);
					$post->setAuthorUrl($data['_embed_authorUrl']);
				}
				$posts[] = $post;
			}
		}
		return $posts;
	}

	/**
	 * @param array $results
	 * @return array
	 */
	public function transformResultFromRawRequest($results) {
		$posts = array();
		if ($results['hits']['total'] > 0) {
			foreach ($results['hits']['hits'] as $result) {
				$post = $this->postRepository->findByIdentifier($result['_id']);
				/** @var Post $post */
				if ($post !== NULL) {
					$posts[] = $post;
				}
			}
		}
		return $posts;
	}

	/**
	 * @param array $aggregations
	 * @param string $type
	 * @return array
	 */
	public function transformAggregations($aggregations, $type) {
		$options = array();
		$aggregationSettings = $this->settings['aggregations'][$type];
		foreach ($aggregationSettings as $aggregation) {
			$buckets = Arrays::getValueByPath($aggregations, $aggregation . '.buckets');
			if (empty($buckets)) {
				$buckets = Arrays::getValueByPath($aggregations, $aggregation . '.' . $aggregation . '.buckets');
			}
			if (!empty($buckets)) {
				foreach ($buckets as $item) {
					$key = strtolower($item['key']);
					$value = $item['key'] . ' (' . $item['doc_count'] . ')';
					$options[$aggregation][$key] = $value;
				}
			}
		}
		return $options;
	}

	/**
	 * @param $currentPage
	 * @return integer
	 */
	public function calculateOffset($currentPage) {
		if ($currentPage < 1) {
			$currentPage = 1;
		} elseif ($currentPage > $this->settings['maximumNumberOfPages']) {
			$currentPage = $this->settings['maximumNumberOfPages'];
		}
		$offset = $this->settings['hitsPerPage'] * ($currentPage - 1);
		return $offset;
	}

	/**
	 * If a certain number of links should be displayed, adjust before and after
	 * amounts accordingly.
	 *
	 * @param integer $currentPage
	 * @param integer $numberOfPages
	 * @param integer $totalHits
	 * @return void
	 */
	protected function calculateDisplayRange($currentPage, $numberOfPages, $totalHits) {
		$maximumNumberOfPages = $this->settings['maximumNumberOfPages'];
		if ($maximumNumberOfPages > $numberOfPages) {
			$maximumNumberOfPages = $numberOfPages;
		}
		$delta = floor($maximumNumberOfPages / 2);
		$this->displayRangeStart = $currentPage - $delta;
		$this->displayRangeEnd = $currentPage + $delta + ($maximumNumberOfPages % 2 === 0 ? 1 : 0);
		if ($this->displayRangeStart < 1) {
			$this->displayRangeEnd -= $this->displayRangeStart - 1;
		}
		if ($this->displayRangeEnd > $numberOfPages) {
			$this->displayRangeStart -= ($this->displayRangeEnd - $numberOfPages);
		}
		$this->displayRangeStart = (integer)max($this->displayRangeStart, 1);
		$this->displayRangeEnd = (integer)min($this->displayRangeEnd, $numberOfPages);
	}

	/**
	 * Returns an array with the keys "pages", "current", "numberOfPages", "nextPage" & "previousPage"
	 *
	 * @param integer $currentPage
	 * @param integer $totalHits
	 * @return array
	 */
	public function buildPagination($currentPage, $totalHits) {
		$numberOfPages = ceil($totalHits / $this->settings['hitsPerPage']);
		$this->calculateDisplayRange($currentPage, $numberOfPages, $totalHits);
		$pages = array();
		for ($i = $this->displayRangeStart; $i <= $this->displayRangeEnd; $i++) {
			$pages[] = array('number' => $i, 'isCurrent' => ($i === $currentPage));
		}
		$pagination = array(
				'pages' => $pages,
				'current' => $currentPage,
				'numberOfPages' => $numberOfPages,
				'displayRangeStart' => $this->displayRangeStart,
				'displayRangeEnd' => $this->displayRangeEnd,
				'hasLessPages' => $this->displayRangeStart > 2,
				'hasMorePages' => $this->displayRangeEnd + 1 < $numberOfPages
		);
		if ($currentPage < $numberOfPages) {
			$pagination['nextPage'] = $currentPage + 1;
		}
		if ($currentPage > 1) {
			$pagination['previousPage'] = $currentPage - 1;
		}
		return $pagination;
	}

	/**
	 * @return Client
	 */
	public function createClient() {
		$client = $this->settings['client'];
		if (!empty($client['username'])) {
			$client['password'] = isset($client['password']) ? $client['password'] : '';
			$authHeaderValue = 'Basic ' . $this->encodeAuth($client['username'], $client['password']) . '==';
			$authHeader = array('Authorization'=>$authHeaderValue);
			$config['headers'] = $authHeader;
		}

		$config['host'] = $client['host'];
		$config['port'] = $client['port'];
		$config['transport'] = $client['scheme'];

		return new Client($config);
	}

	/**
	 * @param $userName
	 * @param $password
	 * @return string
	 */
	function encodeAuth($userName, $password){
		$encodedAuth = base64_encode($userName.':'.$password);
		return $encodedAuth;
	}
}