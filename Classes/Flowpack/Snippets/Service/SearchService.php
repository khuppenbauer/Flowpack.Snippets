<?php
namespace Flowpack\Snippets\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Elastica\Aggregation\Terms;
use Elastica\Aggregation\Filter;
use Elastica\Filter\Bool;
use Elastica\Filter\Term;
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
	 * The index name to be used for querying (by default "typo3cr")
	 *
	 * @var string
	 */
	protected $settings;

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
		foreach ($this->settings['aggregations'] as $aggregation) {
			$aggTerm = new Terms($aggregation);
			$aggTerm->setField($aggregation . '.raw');
			$aggTerm->setSize($aggregationSize);

			$aggFilter = new Filter($aggregation);
			$elasticaFilterBool = new Bool();
			foreach ($filter as $filterName => $filterValue) {
				if (!empty($filterValue) && $filterName !== $aggregation) {
					$elasticaFilter = new Term();
					$elasticaFilter->setTerm($filterName, $filterValue);
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
		$elasticaFilterBool = new Bool();
		foreach ($filter as $filterName => $filterValue) {
			if (!empty($filterValue)) {
				$elasticaFilter = new Term();
				$elasticaFilter->setTerm($filterName, $filterValue);
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
		$elasticaFilterAnd = new Bool();
		foreach ($filter as $filterName => $filterValue) {
			if (!empty($filterValue)) {
				$elasticaFilter = new Term();
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
		$termsAgg = new Terms('tags');
		$termsAgg->setField('tags.raw');
		$termsAgg->setSize($size);
		$elasticaQuery->addAggregation($termsAgg);

		// search
		$resultSet = $elasticaType->search($elasticaQuery);
		return $resultSet;
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
	 * @return array
	 */
	public function transformAggregations($aggregations) {
		$options = array();
		foreach ($this->settings['aggregations'] as $aggregation) {
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