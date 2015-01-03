<?php
namespace Flowpack\Snippets\Domain\Repository;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\Repository;
use Flowpack\Snippets\Domain\Model\User;
use Flowpack\Snippets\Domain\Model\Post;

/**
 * @Flow\Scope("singleton")
 */
class NotificationRepository extends Repository {

	/**
	 * Doctrine's Entity Manager. Note that "ObjectManager" is the name of the related
	 * interface ...
	 *
	 * @Flow\Inject
	 * @var \Doctrine\Common\Persistence\ObjectManager
	 */
	protected $entityManager;

	/**
	 * @param User $target
	 * @param string $type
	 * @param Post $post
	 * @param User $source
	 * @return Post
	 */
	public function findByTargetAndTypeAndPostAndSource(User $target, $type, Post $post = NULL, User $source) {
		$query = $this->createQuery();
		$query->matching(
			$query->logicalAnd(
				$query->equals('target', $target),
				$query->equals('type', $type),
				$query->equals('post', $post),
				$query->equals('source', $source)
			)
		);
		return $query->execute()->getFirst();
	}

	/**
	 * Deletes all Notification for the post
	 *
	 * @param Post $post
	 */
	public function deleteByPost(Post $post) {
		$this->entityManager
			->createQuery('DELETE FROM \Flowpack\Snippets\Domain\Model\Notification n WHERE n.post = :post')
			->execute(array('post' => $post));
	}
}