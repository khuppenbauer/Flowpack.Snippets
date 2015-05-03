<?php
namespace Flowpack\Snippets\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Http\Client\CurlEngine;
use TYPO3\Flow\Mvc\Controller\ActionController;
use Flowpack\Snippets\Domain\Repository\PostRepository;

/**
 * Class PackagistController
 *
 * @package Flowpack\Snippets\Controller
 */
class PackagistController extends ActionController {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Http\Client\Browser
	 */
	protected $browser;

	/**
	 * @Flow\Inject
	 * @var PostRepository
	 */
	protected $postRepository;

	/**
	 * @var string
	 */
	protected $packagistUrl = 'https://packagist.org';

	/**
	 * @return void
	 */
	public function initializeObject() {
		$requestEngine = new CurlEngine();
		$this->browser->setRequestEngine($requestEngine);
	}

	/**
	 */
	public function listAction() {

	}

	/**
	 * @param integer $start
	 * @param integer $length
	 * @param array $search
	 * @return string
	 */
	public function searchAction($start, $length, $search = array()) {
		$page = ($start/$length)+1;
		$filters['page'] = $page;
		$filters['type'] = $search['value'];
		$contentArray = $this->searchPackagist($filters);
		if ($contentArray['total'] === 0) {
			$data['data'] = array();
		} else {
			foreach ($contentArray['results'] as $key => $item) {
				$data['data'][$key]['title'] = '<a href="' . $this->packagistUrl . '/packages/' . $item['name'] . '" target="_blank">' . $item['name'] . '</a>';
				$data['data'][$key]['description'] = $item['description'];
				$data['data'][$key]['downloads'] = '<i class="fa fa-download"></i>' . $item['downloads'];
				$data['data'][$key]['favers'] = '<i class="fa fa-star"></i>' . $item['favers'];
				$disabled = $this->postRepository->findOneByPackage($item['name']) !== NULL ? ' disabled' : '';
				$item['repository'] = str_replace(array('git@github.com:'), 'https://github.com/', $item['repository']);
				$item['repository'] = str_replace(array('git://'), 'https://', $item['repository']);
				$data['data'][$key]['button'] = '<a href="#" data-title="' . $item['name'] . '" data-description="' . $item['description'] . '" data-url="' . $item['repository'] . '" class="jq-take btn btn-default' . $disabled . '"><i class="fa fa-check"></i></a>';
			}
		}
		$data['recordsTotal'] = $contentArray['total'];
		$data['recordsFiltered'] = $contentArray['total'];
		$data['uri'] = $contentArray['url'];
		return json_encode($data);
	}

	/**
	 * @param $filters
	 * @return array
	 */
	protected function searchPackagist($filters) {
		$url = $this->packagistUrl . '/search.json?' . http_build_query($filters);
		$response = $this->browser->request($url);
		$content = $response->getContent();
		$contentArray = json_decode($content, TRUE);
		$contentArray['url'] = $url;
		if ($contentArray['total'] === 0) {
			if (isset($filters['type'])) {
				$filters['q'] = $filters['type'];
				unset($filters['type']);
				return $this->searchPackagist($filters);
			} else {
				return $contentArray;
			}
		} else {
			return $contentArray;
		}
	}


}