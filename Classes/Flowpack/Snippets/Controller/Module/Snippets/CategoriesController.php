<?php
namespace Flowpack\Snippets\Controller\Module\Snippets;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Neos\Controller\Module\AbstractModuleController;
use Flowpack\Snippets\Domain\Model\Category;
use Flowpack\Snippets\Domain\Repository\CategoryRepository;

/**
 * Class CategoriesController
 *
 * @package Flowpack\Snippets\Controller\Module\Snippets
 */
class CategoriesController extends AbstractModuleController {

	/**
	 * @Flow\Inject
	 * @var CategoryRepository
	 */
	protected $categoryRepository;

	/**
	 * @return void
	 */
	public function indexAction() {
		$this->view->assign('categories', $this->categoryRepository->findAll());
	}

	/**
	 * @param Category $category
	 * @return void
	 */
	public function showAction(Category $category) {
		$this->view->assign('category', $category);
	}

	/**
	 * @return void
	 */
	public function newAction() {
	}

	/**
	 * @param Category $newCategory
	 * @return void
	 */
	public function createAction(Category $newCategory) {
		$this->categoryRepository->add($newCategory);
		$this->addFlashMessage('Created a new category.');
		$this->redirect('index');
	}

	/**
	 * @param Category $category
	 * @return void
	 */
	public function editAction(Category $category) {
		$this->view->assign('category', $category);
	}

	/**
	 * @param Category $category
	 * @return void
	 */
	public function updateAction(Category $category) {
		$this->categoryRepository->update($category);
		$this->addFlashMessage('Updated the category.');
		$this->redirect('index');
	}

	/**
	 * @param Category $category
	 * @return void
	 */
	public function deleteAction(Category $category) {
		$this->categoryRepository->remove($category);
		$this->addFlashMessage('Deleted a category.');
		$this->redirect('index');
	}

}