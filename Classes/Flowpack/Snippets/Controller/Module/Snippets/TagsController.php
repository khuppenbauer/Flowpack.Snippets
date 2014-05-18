<?php
namespace Flowpack\Snippets\Controller\Module\Snippets;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Neos\Controller\Module\AbstractModuleController;
use Flowpack\Snippets\Domain\Model\Tag;
use Flowpack\Snippets\Domain\Repository\TagRepository;

/**
 * Class TagsController
 *
 * @package Flowpack\Snippets\Controller\Module\Snippets
 */
class TagsController extends AbstractModuleController {

	/**
	 * @Flow\Inject
	 * @var TagRepository
	 */
	protected $tagRepository;

	/**
	 * @return void
	 */
	public function indexAction() {
		$this->view->assign('tags', $this->tagRepository->findAll());
	}

	/**
	 * @param Tag $tag
	 * @return void
	 */
	public function showAction(Tag $tag) {
		$this->view->assign('tag', $tag);
	}

	/**
	 * @return void
	 */
	public function newAction() {
	}

	/**
	 * @param Tag $newTag
	 * @return void
	 */
	public function createAction(Tag $newTag) {
		$this->tagRepository->add($newTag);
		$this->addFlashMessage('Created a new tag.');
		$this->redirect('index');
	}

	/**
	 * @param Tag $tag
	 * @return void
	 */
	public function editAction(Tag $tag) {
		$this->view->assign('tag', $tag);
	}

	/**
	 * @param Tag $tag
	 * @return void
	 */
	public function updateAction(Tag $tag) {
		$this->tagRepository->update($tag);
		$this->addFlashMessage('Updated the tag.');
		$this->redirect('index');
	}

	/**
	 * @param Tag $tag
	 * @return void
	 */
	public function deleteAction(Tag $tag) {
		$this->tagRepository->remove($tag);
		$this->addFlashMessage('Deleted a tag.');
		$this->redirect('index');
	}

}