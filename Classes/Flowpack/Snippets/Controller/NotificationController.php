<?php
namespace Flowpack\Snippets\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use Flowpack\Snippets\Domain\Model\Notification;
use Flowpack\Snippets\Service\NotificationService;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\Controller\ActionController;
use Flowpack\Snippets\Service\UserService;
use Flowpack\Snippets\Service\SearchService;
use Flowpack\Snippets\Service\EventService;
use Flowpack\Snippets\Domain\Repository\NotificationRepository;
use Flowpack\Snippets\Domain\Model\User;
use TYPO3\Flow\Utility\Arrays;

/**
 * Class NotificationController
 *
 * @package Flowpack\Snippets\Controller
 */
class NotificationController extends ActionController {

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
	 * @var NotificationRepository
	 */
	protected $notificationRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 *
	 */
	public function indexAction() {
		/** @var User $user */
		$user = $this->userService->getUser();
		$notifications = $this->notificationRepository->findByTarget($user);
		$this->view->assign('notifications', $notifications);
	}

	/**
	 *
	 */
	public function countAction() {
		/** @var User $user */
		$user = $this->userService->getUser();
		$state = NotificationService::NOTIFICATION_NEW;
		$result = $this->searchService->notificationCountSearch($user, $state);
		$aggregations = $result->getAggregations();
		$buckets = Arrays::getValueByPath($aggregations, 'type.buckets');
		if (!empty($buckets)) {
			$this->view->assign('count', count($buckets));
		}
	}

	/**
	 *
	 */
	public function listAction() {
		/** @var User $user */
		$user = $this->userService->getUser();
		$state = NotificationService::NOTIFICATION_NEW;
		$result = $this->searchService->notificationListSearch($user, $state);
		$aggregations = $result->getAggregations();
		$buckets = Arrays::getValueByPath($aggregations, 'type.buckets');
		if (!empty($buckets)) {
			foreach ($buckets as $bucket) {
				$events[$bucket['key']] = $bucket['doc_count'];
				foreach ($bucket['hits']['hits']['hits'] as $hit) {
					/** @var Notification $notification */
					$notification = $this->notificationRepository->findByIdentifier($hit['_id']);
					$notification->setState(NotificationService::NOTIFICATION_READ);
					$this->notificationRepository->update($notification);
					$this->emitNotificationUpdated($notification);
				}
			}
			$this->view->assign('events', $events);
			$this->view->assign('count', count($buckets));
			$this->persistenceManager->persistAll();
		}
	}

	/**
	 * Signal that a notification has been removed
	 *
	 * @Flow\Signal
	 * @param Notification $notification
	 * @return void
	 */
	protected function emitNotificationUpdated(Notification $notification) {}


}