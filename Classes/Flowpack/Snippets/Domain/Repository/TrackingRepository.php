<?php
namespace Flowpack\Snippets\Domain\Repository;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use Flowpack\Snippets\Domain\Model\Post;
use Flowpack\Snippets\Domain\Model\Tracking;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\Repository;

/**
 * @Flow\Scope("singleton")
 */
class TrackingRepository extends Repository {

	/**
	 * Finds already tracked posts
	 *
	 * @param Post $post
	 * @param $ipHash
	 * @return Tracking
	 */
	public function findByPostAndIpHash(Post $post, $ipHash){
		$query = $this->createQuery();
		$query->matching(
			$query->logicalAnd(
				$query->equals('post', $post),
				$query->equals('ipHash', $ipHash)
			)
		);
		return $query->execute()->getFirst();
	}

}