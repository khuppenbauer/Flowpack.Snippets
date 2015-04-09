<?php
namespace Flowpack\Snippets\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\Controller\ActionController;
use Flowpack\Snippets\Service\UserService;
use Flowpack\Snippets\Service\NotificationService;
use Flowpack\Snippets\Domain\Repository\CategoryRepository;
use Flowpack\Snippets\Domain\Repository\TagRepository;

/**
 * Class PostController
 *
 * @package Flowpack\Snippets\Controller
 */
class ProfileController extends ActionController {

	/**
	 * @Flow\Inject
	 * @var UserService
	 */
	protected $userService;

	/**
	 * @Flow\Inject
	 * @var NotificationService
	 */
	protected $notificationService;

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
	 * @return void
	 */
	public function listFavoritesAction() {
		$this->view->assign('favorites', $this->notificationService->getUserFavoredPosts());
	}

	/**
	 * @return void
	 */
	public function listFollowersAction() {
		$this->view->assign('followers', $this->notificationService->getFollowers());
	}

	/**
	 * @return void
	 */
	public function listFollowingAction() {
		$this->view->assign('following', $this->notificationService->getFollowing());
	}

	/**
	 * @return void
	 */
	public function listCategoriesAction() {
		$this->view->assign('categories', $this->categoryRepository->findAll());
	}

	/**
	 * @return void
	 */
	public function listTagsAction() {
		$this->view->assign('tags', $this->tagRepository->findAll());
	}
}