<?php
namespace Flowpack\Snippets\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use Elastica\Facet\Terms;
use Elastica\Filter\BoolAnd;
use Elastica\Filter\Term;
use TYPO3\Flow\Annotations as Flow;
use Elastica\Client;
use Elastica\Query;
use Elastica\Query\QueryString;
use Elastica\Query\Filtered;
use Elastica\ResultSet;
use Flowpack\Snippets\Domain\Repository\PostRepository;


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
	 * @param array $settings
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings['elasticSearch'];
	}

	/**
	 * @param string $query
	 * @param array $filter
	 * @return ResultSet
	 */
	public function search($query = '*', $filter = array()) {
		$elasticaClient = new Client();
		$elasticaIndex = $elasticaClient->getIndex($this->settings['index']);
		$elasticaType = $elasticaIndex->getType($this->settings['type']);
		$elasticaQuery = new Query();

		// querystring
		$elasticaQueryString  = new QueryString();
		$elasticaQueryString->setQuery($query);
		$elasticaQuery->setQuery($elasticaQueryString);

		// add filter
		foreach ($filter as $filterName => $filterValue) {
			if (!empty($filterValue)) {
				$elasticaFilter = new Term();
				$elasticaFilter->setTerm($filterName, $filterValue);
				$elasticaFilterAnd    = new BoolAnd();
				$elasticaFilterAnd->addFilter($elasticaFilter);
			}
		}
		if (isset($elasticaFilterAnd)) {
			if ($this->settings['filteredQuery'] === TRUE) {
				$elasticaQueryFilter = new Filtered($elasticaQueryString, $elasticaFilterAnd);
				$elasticaQuery->setQuery($elasticaQueryFilter);
			} else {
				$elasticaQuery->setFilter($elasticaFilterAnd);
			}
		}

		// add facets
		foreach ($this->settings['facets'] as $facet) {
			$elasticaFacet = new Terms($facet);
			$elasticaFacet->setField($facet);
			$elasticaFacet->setSize(10);
			$elasticaFacet->setOrder('count');
			$elasticaQuery->addFacet($elasticaFacet);
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
	 * @param array $facets
	 * @return array
	 */
	public function transformFacets($facets) {
		$options = array();
		foreach ($facets as $facetName => $facet) {
			foreach ($facet['terms'] as $item) {
				$key = $item['term'];
				$value = $item['term'] . ' (' . $item['count'] . ')';
				$options[$facetName][$key] = $value;
			}
		}
		return $options;
	}
}