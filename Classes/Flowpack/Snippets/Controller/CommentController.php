<?php
namespace Flowpack\Snippets\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use Flowpack\Snippets\Domain\Model\Comment;
use Flowpack\Snippets\Domain\Model\Post;
use Flowpack\Snippets\Domain\Repository\PostRepository;
use TYPO3\Flow\Security\Context;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\Controller\ActionController;

/**
 * Comments controller for the Snippets package
 *
 */
class CommentController extends ActionController {

	/**
	 * @var Context
	 */
	protected $securityContext;

	/**
	 * @Flow\Inject
	 * @var PostRepository
	 */
	protected $postRepository;

	/**
	 * Injects the Security Context
	 *
	 * @param Context $securityContext
	 * @return void
	 */
	public function injectSecurityContext(Context $securityContext) {
		$this->securityContext = $securityContext;
	}

	/**
	 * Creates a new comment
	 *
	 * @param Post $post The post which will contain the new comment
	 * @param Comment $newComment A fresh Comment object which has not yet been added to the repository
	 * @return void
	 */
	public function createAction(Post $post, Comment $newComment) {
		$author = $this->securityContext->getPartyByType('Flowpack\Snippets\Domain\Model\User');
		$newComment->setAuthor($author);
		$post->addComment($newComment);
		$this->postRepository->update($post);
		$this->emitCommentCreated($newComment, $post);
		$this->redirect('show', 'Post', NULL, array('post' => $post));
	}

	/**
	 * Removes a comment
	 *
	 * @param Post $post
	 * @param Comment $comment
	 * @return void
	 */
	public function deleteAction(Post $post, Comment $comment) {
		$post->removeComment($comment);
		$this->postRepository->update($post);
		$this->redirect('show', 'Post', NULL, array('post' => $post));
	}

	/**
	 * Override getErrorFlashMessage to present nice flash error messages.
	 *
	 * @return \TYPO3\Flow\Error\Message
	 */
	protected function getErrorFlashMessage() {
		switch ($this->actionMethodName) {
			case 'createAction' :
				return new \TYPO3\Flow\Error\Error('Could not create the new comment');
			default :
				return parent::getErrorFlashMessage();
		}
	}

	/**
	 * @param Comment $comment
	 * @param Post $post
	 * @return void
	 * @Flow\Signal
	 */
	protected function emitCommentCreated(Comment $comment, Post $post) {}


}

?>