<?php
namespace Flowpack\Snippets\Domain\Repository;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\QueryInterface;
use TYPO3\Flow\Persistence\Repository;

/**
 * @Flow\Scope("singleton")
 */
class PostRepository extends Repository {

	/**
	 * @var array
	 */
	protected $defaultOrderings = array('date' => QueryInterface::ORDER_DESCENDING);

}