<?php
namespace Flowpack\Snippets\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use Flowpack\Snippets\Domain\Model\Category;
use Flowpack\Snippets\Domain\Model\Post;
use Flowpack\Snippets\Domain\Model\Tag;
use Flowpack\Snippets\Domain\Model\User;
use Flowpack\Snippets\Domain\Repository\CategoryRepository;
use Flowpack\Snippets\Domain\Repository\TagRepository;
use TYPO3\Party\Domain\Repository\PartyRepository;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\Controller\ActionController;
use TYPO3\Flow\Reflection\ObjectAccess;
use TYPO3\Flow\Security\Context;
use TYPO3\Flow\Utility\Arrays;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use Flowpack\Snippets\Service\SearchService;
use Flowpack\Snippets\Service\UserService;

/**
 * Class SearchController
 *
 * @package Flowpack\Snippets\Controller
 */
class TeaserController extends ActionController {

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
	 * @var PartyRepository
	 */
	protected $partyRepository;

	/**
	 * @var Context
	 */
	protected $securityContext;

	/**
	 * @Flow\Inject
	 * @var SearchService
	 */
	protected $searchService;

	/**
	 * @Flow\Inject
	 * @var UserService
	 */
	protected $userService;

	/**
	 * @var string
	 */
	protected $settings;

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
	 * @param array $settings
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings['elasticSearch'];
	}

	/**
	 * converts tags from select2 library
	 */
	public function initializeShowUserAction() {
		$pluginArguments = $this->request->getParentRequest()->getPluginArguments();
		$post = Arrays::getValueByPath($pluginArguments, 'flowpack_snippets-search.post');
		$this->request->setArgument('post', $post);

		/** @var NodeInterface $node */
		$node = $this->request->getInternalArgument('__node');
		$properties = $node->getProperties();
		$this->request->setArgument('properties', $properties);
	}

	/**
	 * converts tags from select2 library
	 */
	public function initializeFollowAction() {
		$pluginArguments = $this->request->getParentRequest()->getPluginArguments();
		$post = Arrays::getValueByPath($pluginArguments, 'flowpack_snippets-search.post');
		$this->request->setArgument('post', $post);

		/** @var NodeInterface $node */
		$node = $this->request->getInternalArgument('__node');
		$properties = $node->getProperties();
		$this->request->setArgument('properties', $properties);
	}

	/**
	 * @param Post $post
	 * @param array $properties
	 * @return string
	 */
	public function showUserAction(Post $post = NULL, $properties = array()) {
		if ($post !== NULL) {
			$user = $this->userService->getUser();
			if ($user !== NULL && $post->getAuthor() !== $user) {
				$this->view->assign('user', $user);
			}
			$this->view->assign('post', $post);
			if (isset($properties['title'])) {
				$this->view->assign('title', $properties['title']);
			}
		}
	}

	/**
	 * @param Post $post
	 * @param array $properties
	 * @return string
	 */
	public function followAction(Post $post = NULL, $properties = array()) {
		$user = $this->userService->getUser();
		if ($user !== NULL && $post !== NULL) {
			$this->view->assign('post', $post);
			if (isset($properties['title'])) {
				$this->view->assign('title', $properties['title']);
			}
		}
	}

	/**
	 * @param Category $category
	 * @return string
	 */
	public function followCategoryAction(Category $category) {
		if ($category->isFollowed() === TRUE) {
			$category->removeFollower();
		} else {
			$category->addFollower();
		}
		$this->categoryRepository->update($category);
		$followed = $category->isFollowed() === TRUE ? '-' : '+';
		return json_encode(array('followed' => $followed));
	}

	/**
	 * @param Tag $tag
	 * @return string
	 */
	public function followTagAction(Tag $tag) {
		if ($tag->isFollowed() === TRUE) {
			$tag->removeFollower();
		} else {
			$tag->addFollower();
		}
		$this->tagRepository->update($tag);
		$followed = $tag->isFollowed() === TRUE ? '-' : '+';
		return json_encode(array('followed' => $followed));
	}

	/**
	 * @param User $user
	 * @return string
	 */
	public function followUserAction(User $user) {
		if ($user->isFollowed() === TRUE) {
			$user->removeFollower();
		} else {
			$user->addFollower();
		}
		$this->partyRepository->update($user);
		$followed = $user->isFollowed() === TRUE ? '-' : '+';
		return json_encode(array('followed' => $followed));
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

}