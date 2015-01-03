<?php
namespace Flowpack\Snippets\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use Flowpack\Snippets\Domain\Model\Category;
use Flowpack\Snippets\Domain\Model\Comment;
use Flowpack\Snippets\Domain\Model\Event;
use Flowpack\Snippets\Domain\Model\Post;
use Flowpack\Snippets\Domain\Model\Tag;
use Flowpack\Snippets\Domain\Model\User;
use TYPO3\Flow\Annotations as Flow;
use Flowpack\Snippets\Service\UserService;
use Flowpack\Snippets\Domain\Repository\EventRepository;

/**
 * Class EventService
 *
 * @package Flowpack\Snippets\Service
 */
class EventService {

	const POST_CREATED = 'Post.Created';
	const POST_UPDATED = 'Post.Updated';
	const POST_REMOVED = 'Post.Removed';
	const POST_FAVORED = 'Post.Favored';
	const POST_FAVOR_REMOVED = 'Post.Favor.Removed';
	const POST_VOTED_UP = 'Post.Voted.Up';
	const POST_VOTE_UP_REMOVED = 'Post.Vote.Up.Removed';
	const POST_VOTED_DOWN = 'Post.Voted.Down';
	const POST_VOTE_DOWN_REMOVED = 'Post.Vote.Down.Removed';
	const TAG_CREATED = 'Tag.Created';
	const CATEGORY_FOLLOWED = 'Category.Followed';
	const CATEGORY_UNFOLLOWED = 'Category.Unfollowed';
	const TAG_FOLLOWED = 'Tag.Followed';
	const TAG_UNFOLLOWED = 'Tag.Unfollowed';
	const USER_FOLLOWED = 'User.Followed';
	const USER_UNFOLLOWED = 'User.Unfollowed';
	const COMMENT_CREATED = 'Comment.Created';
	const ENTITY_TAG = 'Flowpack\Snippets\Domain\Model\Tag';
	const ENTITY_CATEGORY = 'Flowpack\Snippets\Domain\Model\Category';
	const ENTITY_USER = 'Flowpack\Snippets\Domain\Model\User';
	const ENTITY_COMMENT = 'Flowpack\Snippets\Domain\Model\Comment';

	/**
	 * @Flow\Inject
	 * @var UserService
	 */
	protected $userService;

	/**
	 * @Flow\Inject
	 * @var EventRepository
	 */
	protected $eventRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * @param Post $post
	 */
	public function postCreated(Post $post) {
		$type = self::POST_CREATED;
		$this->createEvent($type, $post);
	}

	/**
	 * @param Post $post
	 */
	public function postUpdated(Post $post) {
		$type = self::POST_UPDATED;
		$this->createEvent($type, $post);
	}

	/**
	 * @param Post $post
	 */
	public function postRemoved(Post $post) {
		$type = self::POST_REMOVED;
		$this->createEvent($type, $post);
	}

	/**
	 * @param Post $post
	 */
	public function postFavored(Post $post) {
		$type = self::POST_FAVORED;
		$this->createEvent($type, $post);
	}

	/**
	 * @param Post $post
	 */
	public function postFavorRemoved(Post $post) {
		$type = self::POST_FAVOR_REMOVED;
		$this->createEvent($type, $post);
	}

	/**
	 * @param Post $post
	 */
	public function postVotedUp(Post $post) {
		$type = self::POST_VOTED_UP;
		$this->createEvent($type, $post);
	}

	/**
	 * @param Post $post
	 */
	public function postVoteUpRemoved(Post $post) {
		$type = self::POST_VOTE_UP_REMOVED;
		$this->createEvent($type, $post);
	}

	/**
	 * @param Post $post
	 */
	public function postVotedDown(Post $post) {
		$type = self::POST_VOTED_DOWN;
		$this->createEvent($type, $post);
	}

	/**
	 * @param Post $post
	 */
	public function postVoteDownRemoved(Post $post) {
		$type = self::POST_VOTE_DOWN_REMOVED;
		$this->createEvent($type, $post);
	}

	/**
	 * @param Tag $tag
	 */
	public function tagCreated(Tag $tag) {
		$type = self::TAG_CREATED;
		$entity = self::ENTITY_TAG;
		$entityIdentifier = $this->persistenceManager->getIdentifierByObject($tag);
		$this->createEvent($type, NULL, $entity, $entityIdentifier);
	}

	/**
	 * @param Comment $comment
	 * @param Post $post
	 */
	public function commentCreated(Comment $comment, Post $post) {
		$type = self::COMMENT_CREATED;
		$entity = self::ENTITY_COMMENT;
		$entityIdentifier = $this->persistenceManager->getIdentifierByObject($comment);
		$this->createEvent($type, $post, $entity, $entityIdentifier);
	}

	/**
	 * @param Category $category
	 */
	public function categoryFollowed(Category $category) {
		$type = self::CATEGORY_FOLLOWED;
		$entity = self::ENTITY_CATEGORY;
		$entityIdentifier = $this->persistenceManager->getIdentifierByObject($category);
		$this->createEvent($type, NULL, $entity, $entityIdentifier);
	}

	/**
	 * @param Category $category
	 */
	public function categoryUnfollowed(Category $category) {
		$type = self::CATEGORY_UNFOLLOWED;
		$entity = self::ENTITY_CATEGORY;
		$entityIdentifier = $this->persistenceManager->getIdentifierByObject($category);
		$this->createEvent($type, NULL, $entity, $entityIdentifier);
	}

	/**
	 * @param Tag $tag
	 */
	public function tagFollowed(Tag $tag) {
		$type = self::TAG_FOLLOWED;
		$entity = self::ENTITY_TAG;
		$entityIdentifier = $this->persistenceManager->getIdentifierByObject($tag);
		$this->createEvent($type, NULL, $entity, $entityIdentifier);
	}

	/**
	 * @param Tag $tag
	 */
	public function tagUnfollowed(Tag $tag) {
		$type = self::TAG_UNFOLLOWED;
		$entity = self::ENTITY_TAG;
		$entityIdentifier = $this->persistenceManager->getIdentifierByObject($tag);
		$this->createEvent($type, NULL, $entity, $entityIdentifier);

	}

	/**
	 * @param User $user
	 */
	public function userFollowed(User $user) {
		$type = self::USER_FOLLOWED;
		$entity = self::ENTITY_USER;
		$entityIdentifier = $this->persistenceManager->getIdentifierByObject($user);
		$this->createEvent($type, NULL, $entity, $entityIdentifier);
	}

	/**
	 * @param User $user
	 */
	public function userUnfollowed(User $user) {
		$type = self::USER_UNFOLLOWED;
		$entity = self::ENTITY_USER;
		$entityIdentifier = $this->persistenceManager->getIdentifierByObject($user);
		$this->createEvent($type, NULL, $entity, $entityIdentifier);
	}

	/**
	 * @param string $type
	 * @param Post $post
	 * @param string $entity
	 * @param string $entityIdentifier
	 */
	protected function createEvent($type, Post $post = NULL, $entity = NULL, $entityIdentifier = NULL) {
		$user = $this->userService->getUser();
		$event = new Event($user, $type, $post, $entity, $entityIdentifier);
		$this->eventRepository->add($event);
		$this->emitEventCreated($event);
	}

	/**
	 * Signal that an event has been created
	 *
	 * @Flow\Signal
	 * @param Event $event
	 * @return void
	 */
	protected function emitEventCreated(Event $event) {}

}