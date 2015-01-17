<?php
namespace Flowpack\Snippets\ViewHelpers;

/*                                                                           *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".        *
 *                                                                           *
 *                                                                           */

use TYPO3\Flow\Annotations as Flow;
use Flowpack\Snippets\Service\SearchService;
use TYPO3\Flow\Reflection\ObjectAccess;

/**
 * This view helper checks if the user is author of this post.
 *
 * @api
 */
class ElasticaViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractConditionViewHelper {

	/**
	 * @Flow\Inject
	 * @var SearchService
	 */
	protected $searchService;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * renders <f:then> child if the author is the logged party,
	 * otherwise renders <f:else> child.
	 *
	 * @param object $object The author of the post
	 * @param string $property
	 * @return string the rendered string
	 * @api
	 */
	public function render($object, $property) {
		$id = $this->persistenceManager->getIdentifierByObject($object);
		$res = $this->searchService->idSearch($id);
		return ObjectAccess::getPropertyPath($res, $property);
	}

}


?>