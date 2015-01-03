<?php
namespace Flowpack\Snippets\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\I18n\Translator;
use Flowpack\Snippets\Domain\Model\Comment;
use Flowpack\Snippets\Domain\Model\Post;
use TYPO3\Fluid\View\StandaloneView;
use TYPO3\SwiftMailer\Message;
use TYPO3\Party\Domain\Repository\PartyRepository;
use Elastica\Document;
use Elastica\Percolator;
use Flowpack\Snippets\Domain\Model\Notification;
use Flowpack\Snippets\Domain\Model\User;
use Flowpack\Snippets\Domain\Repository\NotificationRepository;

/**
 * A notification service
 *
 */
class NotificationService {

	const NOTIFICATION_NEW = 'new';
	const NOTIFICATION_READ = 'read';

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @Flow\Inject
	 * @var Translator
	 */
	protected $translator;

	/**
	 * @Flow\Inject
	 * @var UserService
	 */
	protected $userService;

	/**
	 * @Flow\Inject
	 * @var SearchService
	 */
	protected $searchService;

	/**
	 * @Flow\Inject
	 * @var PartyRepository
	 */
	protected $partyRepository;

	/**
	 * @Flow\Inject
	 * @var NotificationRepository
	 */
	protected $notificationRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * @param array $settings
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}

	/**
	 *
	 */
	public function registerQuery() {
		/** @var User $user */
		$user = $this->userService->getUser();
		$userIdentifier = $this->persistenceManager->getIdentifierByObject($user);

		$elasticaClient = $this->searchService->createClient();
		$elasticaIndex = $elasticaClient->getIndex($this->settings['elasticSearch']['index']);

		$elasticaQuery = $this->searchService->generateFollowingQuery($user);
		$perculator = new Percolator($elasticaIndex);
		$perculator->registerQuery($userIdentifier, $elasticaQuery);
	}

	/**
	 * @param Post $post
	 */
	public function matchDoc(Post $post) {
		$elasticaClient = $this->searchService->createClient();
		$elasticaIndex = $elasticaClient->getIndex($this->settings['elasticSearch']['index']);

		$id = $this->persistenceManager->getIdentifierByObject($post);

		$data['category'] = (string)$post->getCategory();
		$data['author'] = (string)$post->getAuthor();
		$tags = $post->getTags();
		foreach ($tags as $tag) {
			$data['tags'][] = (string)$tag;
		}
		$document = new Document($id, $data, $this->settings['elasticSearch']['type'], $this->settings['elasticSearch']['index']);

		$perculator = new Percolator($elasticaIndex);
		$result = $perculator->matchDoc($document, NULL, $this->settings['elasticSearch']['type']);
		foreach ($result as $item) {
			$user = $this->partyRepository->findByIdentifier($item['_id']);
			if ($user !== $post->getAuthor()) {
				$notification = new Notification($user, EventService::POST_CREATED, $post, $post->getAuthor(), self::NOTIFICATION_NEW);
				$this->notificationRepository->add($notification);
				$this->emitNotificationCreated($notification);
			}
		}
	}

	/**
	 * @param Post $post
	 */
	public function postRemoved(Post $post) {
		$this->notificationRepository->deleteByPost($post);
		// @TODO: remove elastic index
	}

	/**
	 * @param Post $post
	 */
	public function postFavored(Post $post) {
		$type = EventService::POST_FAVORED;
		$this->createNotification($type, $post);
	}

	/**
	 * @param Post $post
	 */
	public function postFavorRemoved(Post $post) {
		$type = EventService::POST_FAVORED;
		$this->removeNotification($type, $post);
	}

	/**
	 * @param Post $post
	 */
	public function postVotedUp(Post $post) {
		$type = EventService::POST_VOTED_UP;
		$this->createNotification($type, $post);
	}

	/**
	 * @param Post $post
	 */
	public function postVoteUpRemoved(Post $post) {
		$type = EventService::POST_VOTED_UP;
		$this->removeNotification($type, $post);
	}

	/**
	 * @param Post $post
	 */
	public function postVotedDown(Post $post) {
		$type = EventService::POST_VOTED_DOWN;
		$this->createNotification($type, $post);
	}

	/**
	 * @param Post $post
	 */
	public function postVoteDownRemoved(Post $post) {
		$type = EventService::POST_VOTED_DOWN;
		$this->removeNotification($type, $post);
	}

	/**
	 * @param Comment $comment
	 * @param Post $post
	 */
	public function commentCreated(Comment $comment, Post $post) {
		$type = EventService::COMMENT_CREATED;
		$this->createNotification($type, $post);
	}

	/**
	 * @param User $user
	 */
	public function userFollowed(User $user) {
		$type = EventService::USER_FOLLOWED;
		$this->createNotification($type, NULL, $user);
	}

	/**
	 * @param User $user
	 */
	public function userUnfollowed(User $user) {
		$type = EventService::USER_FOLLOWED;
		$this->removeNotification($type, NULL, $user);
	}

	/**
	 * @param string $type
	 * @param Post $post
	 * @param User $user
	 */
	protected function createNotification($type, Post $post = NULL, User $user = NULL) {
		$sourceUser = $this->userService->getUser();
		if ($post !== NULL && $user === NULL) {
			$user = $post->getAuthor();
		}
		$notification = new Notification($user, $type, $post, $sourceUser, self::NOTIFICATION_NEW);
		$this->notificationRepository->add($notification);
		$this->emitNotificationCreated($notification);
	}

	/**
	 * @param string $type
	 * @param Post $post
	 * @param User $user
	 */
	protected function removeNotification($type, Post $post = NULL, User $user = NULL) {
		$sourceUser = $this->userService->getUser();
		if ($post !== NULL && $user === NULL) {
			$user = $post->getAuthor();
		}
		$notification = $this->notificationRepository->findByTargetAndTypeAndPostAndSource($user, $type, $post, $sourceUser);
		$this->notificationRepository->remove($notification);
		$this->emitNotificationRemoved($notification);
	}

	/**
	 * @param Comment $comment
	 * @param Post $post
	 * @return void
	 */
	public function sendNewCommentNotification(Comment $comment, Post $post) {
		$settings = $this->settings['notification']['comment'];
		$subject = $this->translator->translateById('comment.notification.subject', array($post->getTitle()), NULL, NULL, 'Main', 'Flowpack.Snippets');
		$message = $this->renderMessage($settings, array('post' => $post, 'comment' => $comment));
		$this->sendMail($settings, $subject, $message);
	}

	/**
	 * @param Post $post
	 * @return void
	 */
	public function sendNewPostNotification(Post $post) {
		$settings = $this->settings['notification']['post'];
		$subject = $this->translator->translateById('post.notification.subject', array($post->getTitle()), NULL, NULL, 'Main', 'Flowpack.Snippets');
		$message = $this->renderMessage($settings, array('post' => $post));
		$this->sendMail($settings, $subject, $message);
	}

	/**
	 * @param array $settings
	 * @param string $subject
	 * @param string $message
	 */
	protected function sendMail($settings, $subject, $message) {
		if ($settings['recipientAddress'] === '') return;

		$mail = new Message();
		$mail
			->setFrom(array($settings['senderAddress'] => $settings['senderName']))
			->setTo(array($settings['recipientAddress'] => $settings['recipientName']))
			->setSubject($subject)
			->setBody($message, $settings['format']);
		$mail->send();
	}

	/**
	 * @param array $settings
	 * @param array $params
	 * @return string
	 */
	protected function renderMessage($settings, $params) {
		$standaloneView = new StandaloneView();
		$standaloneView->setTemplatePathAndFilename($settings['templatePathAndFilename']);
		foreach ($params as $key => $value) {
			$standaloneView->assign($key, $value);
		}
		$message = $standaloneView->render();
		if ($settings['format'] === 'text/plain') {
			$message = strip_tags($message);
			$message = html_entity_decode($message);
		}
		return $message;
	}

	/**
	 * Signal that a notification has been created
	 *
	 * @Flow\Signal
	 * @param Notification $notification
	 * @return void
	 */
	protected function emitNotificationCreated(Notification $notification) {}

	/**
	 * Signal that a notification has been removed
	 *
	 * @Flow\Signal
	 * @param Notification $notification
	 * @return void
	 */
	protected function emitNotificationRemoved(Notification $notification) {}

}

?>