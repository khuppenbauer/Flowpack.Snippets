<?php
namespace Flowpack\Snippets\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use Doctrine\Common\Collections\ArrayCollection;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\Controller\ActionController;
use TYPO3\Flow\Security\Context;
use TYPO3\Flow\Property\PropertyMapper;
use Flowpack\Snippets\Domain\Model\Post;
use Flowpack\Snippets\Domain\Model\Tag;
use Flowpack\Snippets\Domain\Repository\PostRepository;
use Flowpack\Snippets\Domain\Repository\CategoryRepository;
use Flowpack\Snippets\Domain\Repository\TagRepository;

/**
 * Class PostController
 *
 * @package Flowpack\Snippets\Controller
 */
class PostController extends ActionController {

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
	 * @var PropertyMapper
	 */
	protected $propertyMapper;

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
	 * @return void
	 */
	public function indexAction() {
		$author = $this->securityContext->getPartyByType('TYPO3\Neos\Domain\Model\User');
		$this->view->assign('posts', $this->postRepository->findByAuthor($author));
	}

	/**
	 * @param Post $post
	 * @return void
	 */
	public function showAction(Post $post) {
		$author = $this->securityContext->getPartyByType('TYPO3\Neos\Domain\Model\User');
		if ($author !== $post->getAuthor()) {
			$views = $post->getViews() + 1;
			$post->setViews($views);
			$this->postRepository->update($post);
		}
		$this->view->assign('post', $post);
	}

	/**
	 * @return void
	 */
	public function newAction() {
		$newPost = new Post();
		$this->view->assign('newPost', $newPost);
		$this->view->assign('categories', $this->categoryRepository->findAll());
		$this->view->assign('tags', $this->tagRepository->findAll());
	}

	/**
	 * @param Post $newPost
	 * @param string $tags
	 * @return void
	 */
	public function createAction(Post $newPost, $tags) {
		$tagsCollection = new ArrayCollection();
		$tags = explode(',', $tags);
		foreach ($tags as $tag) {
			try {
				$tagObject = $this->propertyMapper->convert($tag, 'Flowpack\Snippets\Domain\Model\Tag');
			} catch (\Exception $exception) {
				$tagObject = $this->propertyMapper->convert(array('name' => $tag), 'Flowpack\Snippets\Domain\Model\Tag');
			}
			$tagsCollection->add($tagObject);
		}
		$newPost->setTags($tagsCollection);

		$author = $this->securityContext->getPartyByType('TYPO3\Neos\Domain\Model\User');
		$newPost->setAuthor($author);

		$this->postRepository->add($newPost);
		$this->addFlashMessage('Created a new post.');
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
	 * @param string $tags
	 * @return void
	 */
	public function updateAction(Post $post, $tags) {
		$tagsCollection = new ArrayCollection();
		$tags = explode(',', $tags);
		foreach ($tags as $tag) {
			try {
				$tagObject = $this->propertyMapper->convert($tag, 'Flowpack\Snippets\Domain\Model\Tag');
			} catch (\Exception $exception) {
				$tagObject = $this->propertyMapper->convert(array('name' => $tag), 'Flowpack\Snippets\Domain\Model\Tag');
			}
			$tagsCollection->add($tagObject);
		}
		$post->setTags($tagsCollection);

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