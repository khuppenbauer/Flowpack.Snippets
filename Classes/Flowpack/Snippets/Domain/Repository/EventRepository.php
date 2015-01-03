<?php
namespace Flowpack\Snippets\Domain\Repository;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use Flowpack\Snippets\Domain\Model\User;
use Flowpack\Snippets\Service\EventService;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\Repository;

/**
 * @Flow\Scope("singleton")
 */
class EventRepository extends Repository {

	/**
	 * Finds comments by the users posts
	 *
	 * @param User $user
	 * @param array $type
	 * @return array
	 */
	public function findByUserPostsAndType(User $user, $type){
		$query = $this->createQuery();
		$query->matching(
			$query->logicalAnd(
				$query->equals('post.author', $user),
				$query->in('type', $type),
				$query->logicalNot(
					$query->equals('user', $user)
				)
			)
		);
		$items = $query->execute()->toArray();
		$events = array();
		foreach ($items as $item) {
			$events[$item->getType()][] = $item;
		}
		return $events;
	}

	/**
	 * @param string $userIdentifier
	 * @param string $type
	 * @return \TYPO3\Flow\Persistence\QueryResultInterface
	 */
	public function findByUserAndType($userIdentifier, $type) {
		$query = $this->createQuery();
		$query->matching(
			$query->logicalAnd(
				$query->equals('entityIdentifier', $userIdentifier),
				$query->equals('entity', EventService::ENTITY_USER),
				$query->equals('type', $type)
			)
		);
		return $query->execute();
	}

	/**
	 * @param $followedUser
	 * @param $followedCategories
	 * @param $followedTags
	 * @param $type
	 * @return \TYPO3\Flow\Persistence\QueryResultInterface
	 */
	public function findByFollowingAndType($followedUser, $followedCategories, $followedTags, $type) {
		$query = $this->createQuery();
		$query->matching(
			$query->logicalAnd(
				$query->equals('type', $type)
			),
			$query->logicalOr(
				$query->contains('post.author', $followedUser),
				$query->contains('post.category', $followedCategories),
				$query->contains('post.tags', $followedTags)
			)
		);
		return $query->execute();
	}


}