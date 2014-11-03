<?php
namespace Flowpack\Snippets\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use Elastica\Aggregation\Terms;
use Elastica\Filter\BoolAnd;
use Elastica\Filter\Term;
use TYPO3\Flow\Annotations as Flow;
use Elastica\Client;
use Elastica\Query;
use Elastica\Query\QueryString;
use Elastica\Query\Filtered;
use Elastica\ResultSet;
use Flowpack\Snippets\Domain\Repository\PostRepository;
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
	 * @return ResultSet
	 */
	public function search($query = '*', $filter = array(), $offset = 0) {
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

		// add filter
		$elasticaFilterAnd = new BoolAnd();
		foreach ($filter as $filterName => $filterValue) {
			if (!empty($filterValue)) {
				$elasticaFilter = new Term();
				$elasticaFilter->setTerm($filterName, $filterValue);
				$elasticaFilterAnd->addFilter($elasticaFilter);
			}
		}
		if (count($elasticaFilterAnd->getFilters()) > 0) {
			if ($this->settings['filteredQuery'] === TRUE) {
				$elasticaQueryFilter = new Filtered($elasticaQueryString, $elasticaFilterAnd);
				$elasticaQuery->setQuery($elasticaQueryFilter);
			} else {
				$elasticaQuery->setPostFilter($elasticaFilterAnd);
			}
		}

		// add aggregations
		foreach ($this->settings['aggregations'] as $aggregation) {
			$termsAgg = new Terms($aggregation);
			$termsAgg->setField($aggregation);
			$termsAgg->setSize(10);
			$elasticaQuery->addAggregation($termsAgg);
		}

		// search
		$resultSet = $elasticaType->search($elasticaQuery);
		return $resultSet;
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
	 * @param array $aggregations
	 * @return array
	 */
	public function transformAggregations($aggregations) {
		$options = array();
		foreach ($this->settings['aggregations'] as $aggregation) {
			$buckets = Arrays::getValueByPath($aggregations, $aggregation . '.buckets');
			if (!empty($buckets)) {
				foreach ($buckets as $item) {
					$key = $item['key'];
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