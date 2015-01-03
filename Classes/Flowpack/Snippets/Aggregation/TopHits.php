<?php
namespace Flowpack\Snippets\Aggregation;

use Elastica\Aggregation\AbstractAggregation;

/**
 * Class TopHits
 * @package Elastica\Aggregation
 * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/master/search-aggregations-metrics-top-hits-aggregation.html
 */
class TopHits extends AbstractAggregation {

	/**
	 * Sets the source for the TopHits Aggregation
	 *
	 * @param  array $source The source for the TopHits Aggregation
	 * @return \Flowpack\Snippets\Aggregation\TopHits
	 */
	public function setSource($source) {
		return $this->setParam('_source', $source);
	}

}