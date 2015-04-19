<?php
namespace Flowpack\Snippets;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Core\Booting\Step;
use TYPO3\Flow\Core\Bootstrap;
use TYPO3\Flow\Package\Package as BasePackage;

/**
 * The Snippets Package
 */
class Package extends BasePackage {

	/**
	 * Invokes custom PHP code directly after the package manager has been initialized.
	 *
	 * @param Bootstrap $bootstrap The current bootstrap
	 *
	 * @return void
	 */
	public function boot(Bootstrap $bootstrap) {
		$dispatcher = $bootstrap->getSignalSlotDispatcher();

		//Elasticsearch Post Index
		$dispatcher->connect('Flowpack\Snippets\Controller\PostController', 'postCreated', 'Flowpack\ElasticSearch\Indexer\Object\ObjectIndexer', 'indexObject');
		$dispatcher->connect('Flowpack\Snippets\Controller\PostController', 'postUpdated', 'Flowpack\ElasticSearch\Indexer\Object\ObjectIndexer', 'indexObject');
		$dispatcher->connect('Flowpack\Snippets\Controller\PostController', 'postRemoved', 'Flowpack\ElasticSearch\Indexer\Object\ObjectIndexer', 'removeObject');
		$dispatcher->connect('Flowpack\Snippets\Controller\PostController', 'postFavored', 'Flowpack\ElasticSearch\Indexer\Object\ObjectIndexer', 'updateObject');
		$dispatcher->connect('Flowpack\Snippets\Controller\PostController', 'postFavorRemoved', 'Flowpack\ElasticSearch\Indexer\Object\ObjectIndexer', 'updateObject');
		$dispatcher->connect('Flowpack\Snippets\Controller\PostController', 'postVotedUp', 'Flowpack\ElasticSearch\Indexer\Object\ObjectIndexer', 'updateObject');
		$dispatcher->connect('Flowpack\Snippets\Controller\PostController', 'postVoteUpRemoved', 'Flowpack\ElasticSearch\Indexer\Object\ObjectIndexer', 'updateObject');
		$dispatcher->connect('Flowpack\Snippets\Controller\PostController', 'postVotedDown', 'Flowpack\ElasticSearch\Indexer\Object\ObjectIndexer', 'updateObject');
		$dispatcher->connect('Flowpack\Snippets\Controller\PostController', 'postVoteDownRemoved', 'Flowpack\ElasticSearch\Indexer\Object\ObjectIndexer', 'updateObject');
		$dispatcher->connect('Flowpack\Snippets\Controller\PostController', 'postCounted', 'Flowpack\ElasticSearch\Indexer\Object\ObjectIndexer', 'updateObject');
		$dispatcher->connect('Flowpack\Snippets\Controller\Module\Snippets\PostsController', 'postUpdated', 'Flowpack\ElasticSearch\Indexer\Object\ObjectIndexer', 'indexObject');
		$dispatcher->connect('Flowpack\Snippets\Controller\Module\Snippets\PostsController', 'postRemoved', 'Flowpack\ElasticSearch\Indexer\Object\ObjectIndexer', 'removeObject');

		//Capture external Sites
		$dispatcher->connect('Flowpack\Snippets\Controller\PostController', 'postCreated', 'Flowpack\Snippets\Service\CaptureService', 'createCapture');
		$dispatcher->connect('Flowpack\Snippets\Controller\PostController', 'postUpdated', 'Flowpack\Snippets\Service\CaptureService', 'createCapture');
		$dispatcher->connect('Flowpack\Snippets\Controller\Module\Snippets\PostsController', 'postUpdated', 'Flowpack\Snippets\Service\CaptureService', 'createCapture');

		//Elasticsearch Perculator
		$dispatcher->connect('Flowpack\Snippets\Controller\TeaserController', 'categoryFollowed', 'Flowpack\Snippets\Service\NotificationService', 'registerQuery');
		$dispatcher->connect('Flowpack\Snippets\Controller\TeaserController', 'categoryUnfollowed', 'Flowpack\Snippets\Service\NotificationService', 'registerQuery');
		$dispatcher->connect('Flowpack\Snippets\Controller\TeaserController', 'tagFollowed', 'Flowpack\Snippets\Service\NotificationService', 'registerQuery');
		$dispatcher->connect('Flowpack\Snippets\Controller\TeaserController', 'tagUnfollowed', 'Flowpack\Snippets\Service\NotificationService', 'registerQuery');
		$dispatcher->connect('Flowpack\Snippets\Controller\TeaserController', 'userFollowed', 'Flowpack\Snippets\Service\NotificationService', 'registerQuery');
		$dispatcher->connect('Flowpack\Snippets\Controller\TeaserController', 'userUnfollowed', 'Flowpack\Snippets\Service\NotificationService', 'registerQuery');

		//Eventlog
		$dispatcher->connect('Flowpack\Snippets\Controller\PostController', 'postCreated', 'Flowpack\Snippets\Service\EventService', 'postCreated');
		$dispatcher->connect('Flowpack\Snippets\Controller\PostController', 'postUpdated', 'Flowpack\Snippets\Service\EventService', 'postUpdated');
		$dispatcher->connect('Flowpack\Snippets\Controller\PostController', 'postFavored', 'Flowpack\Snippets\Service\EventService', 'postFavored');
		$dispatcher->connect('Flowpack\Snippets\Controller\PostController', 'postFavorRemoved', 'Flowpack\Snippets\Service\EventService', 'postFavorRemoved');
		$dispatcher->connect('Flowpack\Snippets\Controller\PostController', 'postVotedUp', 'Flowpack\Snippets\Service\EventService', 'postVotedUp');
		$dispatcher->connect('Flowpack\Snippets\Controller\PostController', 'postVoteUpRemoved', 'Flowpack\Snippets\Service\EventService', 'postVoteUpRemoved');
		$dispatcher->connect('Flowpack\Snippets\Controller\PostController', 'postVotedDown', 'Flowpack\Snippets\Service\EventService', 'postVotedDown');
		$dispatcher->connect('Flowpack\Snippets\Controller\PostController', 'postVoteDownRemoved', 'Flowpack\Snippets\Service\EventService', 'postVoteDownRemoved');
		$dispatcher->connect('Flowpack\Snippets\Controller\PostController', 'tagCreated', 'Flowpack\Snippets\Service\EventService', 'tagCreated');
		$dispatcher->connect('Flowpack\Snippets\Controller\CommentController', 'commentCreated', 'Flowpack\Snippets\Service\EventService', 'commentCreated');
		$dispatcher->connect('Flowpack\Snippets\Controller\TeaserController', 'categoryFollowed', 'Flowpack\Snippets\Service\EventService', 'categoryFollowed');
		$dispatcher->connect('Flowpack\Snippets\Controller\TeaserController', 'categoryUnfollowed', 'Flowpack\Snippets\Service\EventService', 'categoryUnfollowed');
		$dispatcher->connect('Flowpack\Snippets\Controller\TeaserController', 'tagFollowed', 'Flowpack\Snippets\Service\EventService', 'tagFollowed');
		$dispatcher->connect('Flowpack\Snippets\Controller\TeaserController', 'tagUnfollowed', 'Flowpack\Snippets\Service\EventService', 'tagUnfollowed');
		$dispatcher->connect('Flowpack\Snippets\Controller\TeaserController', 'userFollowed', 'Flowpack\Snippets\Service\EventService', 'userFollowed');
		$dispatcher->connect('Flowpack\Snippets\Controller\TeaserController', 'userUnfollowed', 'Flowpack\Snippets\Service\EventService', 'userUnfollowed');

		//Email Notification
		//$dispatcher->connect('Flowpack\Snippets\Controller\PostController', 'postCreated', 'Flowpack\Snippets\Service\NotificationService', 'sendNewPostNotification');
		//$dispatcher->connect('Flowpack\Snippets\Controller\CommentController', 'commentCreated', 'Flowpack\Snippets\Service\NotificationService', 'sendNewCommentNotification');

		//Notification
		$dispatcher->connect('Flowpack\Snippets\Controller\PostController', 'postCreated', 'Flowpack\Snippets\Service\NotificationService', 'matchDoc');
		$dispatcher->connect('Flowpack\Snippets\Controller\PostController', 'postRemoved', 'Flowpack\Snippets\Service\NotificationService', 'postRemoved');
		$dispatcher->connect('Flowpack\Snippets\Controller\PostController', 'postFavored', 'Flowpack\Snippets\Service\NotificationService', 'postFavored');
		$dispatcher->connect('Flowpack\Snippets\Controller\PostController', 'postFavorRemoved', 'Flowpack\Snippets\Service\NotificationService', 'postFavorRemoved');
		$dispatcher->connect('Flowpack\Snippets\Controller\PostController', 'postVotedUp', 'Flowpack\Snippets\Service\NotificationService', 'postVotedUp');
		$dispatcher->connect('Flowpack\Snippets\Controller\PostController', 'postVoteUpRemoved', 'Flowpack\Snippets\Service\NotificationService', 'postVoteUpRemoved');
		$dispatcher->connect('Flowpack\Snippets\Controller\PostController', 'postVotedDown', 'Flowpack\Snippets\Service\NotificationService', 'postVotedDown');
		$dispatcher->connect('Flowpack\Snippets\Controller\PostController', 'postVoteDownRemoved', 'Flowpack\Snippets\Service\NotificationService', 'postVoteDownRemoved');
		$dispatcher->connect('Flowpack\Snippets\Controller\CommentController', 'commentCreated', 'Flowpack\Snippets\Service\NotificationService', 'commentCreated');
		$dispatcher->connect('Flowpack\Snippets\Controller\TeaserController', 'userFollowed', 'Flowpack\Snippets\Service\NotificationService', 'userFollowed');
		$dispatcher->connect('Flowpack\Snippets\Controller\TeaserController', 'userUnfollowed', 'Flowpack\Snippets\Service\NotificationService', 'userUnfollowed');

		$dispatcher->connect('Flowpack\Snippets\Service\NotificationService', 'notificationCreated', 'Flowpack\ElasticSearch\Indexer\Object\ObjectIndexer', 'indexObject');
		$dispatcher->connect('Flowpack\Snippets\Controller\NotificationController', 'notificationUpdated', 'Flowpack\ElasticSearch\Indexer\Object\ObjectIndexer', 'indexObject');
		$dispatcher->connect('Flowpack\Snippets\Service\NotificationService', 'notificationRemoved', 'Flowpack\ElasticSearch\Indexer\Object\ObjectIndexer', 'removeObject');

		require(__DIR__ . '/../../../Resources/Private/PHP/Parsedown.php');
	}

}

