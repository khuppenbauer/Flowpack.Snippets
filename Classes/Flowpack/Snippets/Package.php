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

		$dispatcher->connect('Flowpack\Snippets\Controller\PostController', 'postCreated', 'Flowpack\ElasticSearch\Indexer\Object\ObjectIndexer', 'indexObject');
		$dispatcher->connect('Flowpack\Snippets\Controller\PostController', 'postUpdated', 'Flowpack\ElasticSearch\Indexer\Object\ObjectIndexer', 'indexObject');
		$dispatcher->connect('Flowpack\Snippets\Controller\PostController', 'postRemoved', 'Flowpack\ElasticSearch\Indexer\Object\ObjectIndexer', 'removeObject');
		$dispatcher->connect('Flowpack\Snippets\Controller\Module\Snippets\PostsController', 'postUpdated', 'Flowpack\ElasticSearch\Indexer\Object\ObjectIndexer', 'indexObject');
		$dispatcher->connect('Flowpack\Snippets\Controller\Module\Snippets\PostsController', 'postRemoved', 'Flowpack\ElasticSearch\Indexer\Object\ObjectIndexer', 'removeObject');

		$dispatcher->connect('Flowpack\Snippets\Controller\PostController', 'postCreated', 'Flowpack\Snippets\Service\Notification', 'sendNewPostNotification');
		$dispatcher->connect('Flowpack\Snippets\Controller\CommentController', 'commentCreated', 'Flowpack\Snippets\Service\Notification', 'sendNewCommentNotification');

		require(__DIR__ . '/../../../Resources/Private/PHP/Parsedown.php');
	}

}

