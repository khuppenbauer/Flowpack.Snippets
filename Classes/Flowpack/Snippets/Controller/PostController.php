<?php
namespace Flowpack\Snippets\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

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
	 * converts tags from select2 library
	 */
	public function initializeCreateAction() {
		$newPost = $this->request->getArgument('newPost');
		$tags = $this->convertTags();
		if (!empty($tags)) {
			$newPost['tags'] = $tags;
			$this->request->setArgument('newPost', $newPost);
			$this->arguments['newPost']->getPropertyMappingConfiguration()->allowProperties('tags');
			$this->arguments['newPost']->getPropertyMappingConfiguration()->setTypeConverterOption('TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter', \TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED, TRUE);
			$this->arguments['newPost']->getPropertyMappingConfiguration()->forProperty('tags')->allowAllProperties();
			$this->arguments['newPost']->getPropertyMappingConfiguration()->allowModificationForSubProperty('tags');
		}
	}

	/**
	 * converts tags from select2 library
	 */
	public function initializeUpdateAction() {
		$post = $this->request->getArgument('post');
		$tags = $this->convertTags();
		if (!empty($tags)) {
			$post['tags'] = $tags;
		} else {
			$post['tags'] = '';
		}
		$this->request->setArgument('post', $post);
		$this->arguments['post']->getPropertyMappingConfiguration()->allowProperties('tags');
		$this->arguments['post']->getPropertyMappingConfiguration()->setTypeConverterOption('TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter', \TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_MODIFICATION_ALLOWED, TRUE);
		$this->arguments['post']->getPropertyMappingConfiguration()->forProperty('tags')->allowAllProperties();
		$this->arguments['post']->getPropertyMappingConfiguration()->allowModificationForSubProperty('tags');
	}

	/**
	 * @return array
	 */
	protected function convertTags() {
		$tags = $this->request->getArgument('tags');
		if (!empty($tags)) {
			$tagsArray = explode(',', $tags);
			foreach ($tagsArray as $key => $tag) {
				if (!((preg_match('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/', $tag)) ||
						(preg_match('/[0-9a-f]{40}/', $tag)))) {
					$tagObject = new Tag($tag);
					$this->tagRepository->add($tagObject);
					$tagsArray[$key] = $this->persistenceManager->getIdentifierByObject($tagObject);
				}
			}
			return $tagsArray;
		}
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
		$author = $this->securityContext->getPartyByType('TYPO3\Neos\Domain\Model\User');
		$newPost->setAuthor($author);
		$this->postRepository->add($newPost);
		$this->emitPostUpdated($newPost);
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
		$this->postRepository->update($post);
		if ($post->isActive() === TRUE) {
			$this->emitPostUpdated($post);
		} else {
			$this->emitPostRemoved($post);
		}
		$this->redirect('index');
	}

	/**
	 * @param Post $post
	 * @return void
	 */
	public function deleteAction(Post $post) {
		$post->removeTags();
		$this->postRepository->remove($post);
		$this->emitPostRemoved($post);
		$this->redirect('index');
	}

	/**
	 * Signals that a post was updated.
	 *
	 * @Flow\Signal
	 * @param Post $post
	 * @return void
	 */
	protected function emitPostUpdated(Post $post) {
	}

	/**
	 * Signals that a post was removed.
	 *
	 * @Flow\Signal
	 * @param Post $post
	 * @return void
	 */
	protected function emitPostRemoved(Post $post) {
	}

	/**
	 * @return boolean Disable the default error flash message
	 */
	protected function getErrorFlashMessage() {
		return FALSE;
	}
}