<?php
namespace Flowpack\Snippets\Controller\Module\Snippets;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Error\Message;
use TYPO3\Neos\Controller\Module\AbstractModuleController;
use Flowpack\Snippets\Domain\Model\Post;
use Flowpack\Snippets\Domain\Model\PostType;
use Flowpack\Snippets\Domain\Repository\PostRepository;
use Flowpack\Snippets\Domain\Repository\CategoryRepository;
use Flowpack\Snippets\Domain\Repository\TagRepository;
use Flowpack\ElasticSearch\Domain\Factory\ClientFactory;
use Flowpack\ElasticSearch\Domain\Model\Document;

/**
 * Class PostsController
 *
 * @package Flowpack\Snippets\Controller\Module\Snippets
 */
class PostsController extends AbstractModuleController {

	/**
	 * @Flow\Inject
	 * @var PostRepository
	 */
	protected $postRepository;

	/**
	 * @Flow\Inject
	 * @var CategoryRepository
	 */
	protected $categoryRepository;

	/**
	 * @Flow\Inject
	 * @var TagRepository
	 */
	protected $tagRepository;

	/**
	 * @Flow\Inject
	 * @var ClientFactory
	 */
	protected $clientFactory;

	/**
	 * @return void
	 */
	public function indexAction() {
		$this->view->assign('posts', $this->postRepository->findAll());
	}

	/**
	 * @param Post $post
	 * @return void
	 */
	public function showAction(Post $post) {
		$this->view->assign('post', $post);
	}

	/**
	 * @param Post $post
	 * @return void
	 */
	public function activateAction(Post $post) {
		$post->setActive(TRUE);
		$this->postRepository->update($post);
		$this->addFlashMessage('The post has been activated.');
		$this->redirect('index');
	}

	/**
	 * @param Post $post
	 * @return void
	 */
	public function deactivateAction(Post $post) {
		$post->setActive(FALSE);
		$this->postRepository->update($post);
		$this->addFlashMessage('The post has been deactivated.');
		$this->redirect('index');
	}

	/**
	 * @param Post $post
	 * @return void
	 */
	public function editAction(Post $post) {
		$this->view->assign('post', $post);
		$this->view->assign('categories', $this->categoryRepository->findAll());
		$this->view->assign('tags', $this->tagRepository->findAll());
	}

	/**
	 * @param Post $post
	 * @return void
	 */
	public function updateAction(Post $post) {
		$this->postRepository->update($post);
		$this->addFlashMessage('Updated the post.');
		$this->redirect('index');
	}

	/**
	 * @param Post $post
	 * @return void
	 */
	public function deleteAction(Post $post) {
		$post->removeTags();
		$this->postRepository->remove($post);
		$this->addFlashMessage('Deleted a post.');
		$this->redirect('index');
	}

}