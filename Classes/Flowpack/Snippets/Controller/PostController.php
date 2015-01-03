<?php
namespace Flowpack\Snippets\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use Flowpack\Snippets\Domain\Model\Tracking;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\Controller\ActionController;
use TYPO3\Flow\Security\Context;
use TYPO3\Flow\Property\PropertyMapper;
use Flowpack\Snippets\Service\UserService;
use Flowpack\Snippets\Domain\Model\Post;
use Flowpack\Snippets\Domain\Model\Tag;
use Flowpack\Snippets\Domain\Repository\PostRepository;
use Flowpack\Snippets\Domain\Repository\CategoryRepository;
use Flowpack\Snippets\Domain\Repository\TagRepository;
use Flowpack\Snippets\Domain\Repository\TrackingRepository;

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
	 * @var TrackingRepository
	 */
	protected $trackingRepository;

	/**
	 * @Flow\Inject
	 * @var UserService
	 */
	protected $userService;

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
		$tags = $this->convertTags($newPost['tags']);
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
		$tags = $this->convertTags($post['tags']);
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
	 * Initializes the voteUp Action
	 */
	public function initializeVoteUpAction() {
		$this->arguments['post']->getPropertyMappingConfiguration()->allowProperties('upVotes');
		$this->arguments['post']->getPropertyMappingConfiguration()->setTypeConverterOption('TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter', \TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_MODIFICATION_ALLOWED, TRUE);
		$this->arguments['post']->getPropertyMappingConfiguration()->forProperty('upVotes')->allowAllProperties();
		$this->arguments['post']->getPropertyMappingConfiguration()->allowModificationForSubProperty('upVotes');
	}

	/**
	 * Initializes the voteDown Action
	 */
	public function initializeVoteDownAction() {
		$this->arguments['post']->getPropertyMappingConfiguration()->allowProperties('downVotes');
		$this->arguments['post']->getPropertyMappingConfiguration()->setTypeConverterOption('TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter', \TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_MODIFICATION_ALLOWED, TRUE);
		$this->arguments['post']->getPropertyMappingConfiguration()->forProperty('downVotes')->allowAllProperties();
		$this->arguments['post']->getPropertyMappingConfiguration()->allowModificationForSubProperty('downVotes');
	}

	/**
	 * Initializes the favorite Action
	 */
	public function initializeFavoriteAction() {
		$this->arguments['post']->getPropertyMappingConfiguration()->allowProperties('favorites');
		$this->arguments['post']->getPropertyMappingConfiguration()->setTypeConverterOption('TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter', \TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_MODIFICATION_ALLOWED, TRUE);
		$this->arguments['post']->getPropertyMappingConfiguration()->forProperty('favorites')->allowAllProperties();
		$this->arguments['post']->getPropertyMappingConfiguration()->allowModificationForSubProperty('favorites');
	}

	/**
	 * @param string $tags
	 * @return array
	 */
	protected function convertTags($tags) {
		if (!empty($tags)) {
			$tagsArray = explode(',', $tags);
			foreach ($tagsArray as $key => $tag) {
				if ($this->persistenceManager->getObjectByIdentifier($tag, 'Flowpack\Snippets\Domain\Model\Tag', FALSE) === NULL) {
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
		$author = $this->userService->getUser();
		$this->view->assign('posts', $this->postRepository->findByAuthor($author));
	}

	/**
	 * @param Post $post
	 * @return void
	 */
	public function showAction(Post $post) {
		$user = $this->userService->getUser();
		$this->view->assign('user', $user);
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
	 * @Flow\Validate(argumentName="newPost", type="\Flowpack\Snippets\Validation\Validator\NotEmptyByTypeValidator")
	 * @return void
	 */
	public function createAction(Post $newPost) {
		$author = $this->userService->getUser();
		$newPost->setAuthor($author);
		$this->postRepository->add($newPost);
		$this->emitPostCreated($newPost);
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
		$post->setActive(FALSE);
		$this->postRepository->update($post);
		$this->emitPostRemoved($post);
		$this->redirect('index');
	}

	/**
	 * @param Post $post
	 * @return string
	 */
	public function voteUpAction(Post $post) {
		if ($post->hasUpVote() === TRUE) {
			$post->removeUpVote();
			$this->emitPostVoteUpRemoved($post);
		} else {
			$post->addUpVote();
			$this->emitPostVotedUp($post);
			if ($post->hasDownVote() === TRUE) {
				$post->removeDownVote();
				$this->emitPostVoteDownRemoved($post);
			}
		}
		$this->postRepository->update($post);
		return $this->responseData($post);
	}

	/**
	 * @param Post $post
	 * @return string
	 */
	public function voteDownAction(Post $post) {
		if ($post->hasDownVote() === TRUE) {
			$post->removeDownVote();
			$this->emitPostVoteDownRemoved($post);
		} else {
			$post->addDownVote();
			$this->emitPostVotedDown($post);
			if ($post->hasUpVote() === TRUE) {
				$post->removeUpVote();
				$this->emitPostVoteUpRemoved($post);
			}
		}
		$this->postRepository->update($post);
		return $this->responseData($post);
	}

	/**
	 * @param Post $post
	 * @return string
	 */
	public function favorAction(Post $post) {
		if ($post->isFavorite() === TRUE) {
			$post->removeFavorite();
			$this->emitPostFavorRemoved($post);
		} else {
			$post->addFavorite();
			$this->emitPostFavored($post);
		}
		$this->postRepository->update($post);
		return $this->responseData($post);
	}

	/**
	 * @param Post $post
	 * @return string
	 */
	public function countViewsAction(Post $post) {
		$ipHash = sha1($_SERVER['REMOTE_ADDR']);
		$tracking = $this->trackingRepository->findByPostAndIpHash($post, $ipHash);
		if ($tracking === NULL) {
			$tracking = new Tracking($post, $ipHash);
			$post->addView($tracking);
			$this->postRepository->update($post);
			$this->emitPostCounted($post);
		}
		return $this->responseData($post);
	}

	/**
	 * @param Post $post
	 * @return string
	 */
	protected function responseData(Post $post) {
		$data['upVotes'] = $post->getNumberOfUpVotes();
		$data['downVotes'] = $post->getNumberOfDownVotes();
		$data['favor'] = $post->isFavorite();
		$data['up'] = $post->hasUpVote();
		$data['down'] = $post->hasDownVote();
		$data['favorites'] = $post->getNumberOfFavorites();
		$data['views'] = $post->getNumberOfViews();
		return json_encode($data);
	}

	/**
	 * Signals that a post was updated.
	 *
	 * @Flow\Signal
	 * @param Post $post
	 * @return void
	 */
	protected function emitPostCreated(Post $post) {}

	/**
	 * Signals that a post was updated.
	 *
	 * @Flow\Signal
	 * @param Post $post
	 * @return void
	 */
	protected function emitPostUpdated(Post $post) {}

	/**
	 * Signals that a post was removed.
	 *
	 * @Flow\Signal
	 * @param Post $post
	 * @return void
	 */
	protected function emitPostRemoved(Post $post) {}

	/**
	 * Signals that a tag has been added
	 *
	 * @Flow\Signal
	 * @param Tag $tag
	 * @return void
	 */
	protected function emitTagAdded(Tag $tag) {}

	/**
	 * Signals that a post has been favored
	 *
	 * @Flow\Signal
	 * @param Post $post
	 * @return void
	 */
	protected function emitPostFavored(Post $post) {}

	/**
	 * Signals that a post has been favored
	 *
	 * @Flow\Signal
	 * @param Post $post
	 * @return void
	 */
	protected function emitPostFavorRemoved(Post $post) {}

	/**
	 * Signal that a post has been voted up
	 *
	 * @Flow\Signal
	 * @param Post $post
	 * @return void
	 */
	protected function emitPostVotedUp(Post $post) {}

	/**
	 * Signal that a post has been voted up
	 *
	 * @Flow\Signal
	 * @param Post $post
	 * @return void
	 */
	protected function emitPostVoteUpRemoved(Post $post) {}

	/**
	 * Signal that a post has been voted down
	 *
	 * @Flow\Signal
	 * @param Post $post
	 * @return void
	 */
	protected function emitPostVotedDown(Post $post) {}

	/**
	 * Signal that a post has been voted down
	 *
	 * @Flow\Signal
	 * @param Post $post
	 * @return void
	 */
	protected function emitPostVoteDownRemoved(Post $post) {}

	/**
	 * Signal that a post has been counted
	 *
	 * @Flow\Signal
	 * @param Post $post
	 * @return void
	 */
	protected function emitPostCounted(Post $post) {}

	/**
	 * @return boolean Disable the default error flash message
	 */
	protected function getErrorFlashMessage() {
		return FALSE;
	}
}