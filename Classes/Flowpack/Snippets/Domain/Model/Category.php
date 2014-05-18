<?php
namespace Flowpack\Snippets\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * A Snippet category
 *
 * @Flow\Entity
 */
class Category {

	/**
	 * The category name
	 *
	 * @var string
	 * @Flow\Validate(type="StringLength", options={"minimum"=1, "maximum"=255})
	 * @ORM\Column(length=255)
	 */
	protected $name;

	/**
	 * The category parent
	 *
	 * @var Category
	 * @ORM\ManyToOne(inversedBy="children")
	 */
	protected $parent;

	/**
	 * The category children
	 *
	 * @var Collection<\Flowpack\Snippets\Domain\Model\Category>
	 * @ORM\OneToMany(mappedBy="parent",cascade={"remove"})
	 */
	protected $children;

	/**
	 * The category posts
	 *
	 * @var Collection<\Flowpack\Snippets\Domain\Model\Post>
	 * @ORM\OneToMany(mappedBy="category")
	 */
	protected $posts;

	/**
	 * Constructs this category
	 */
	public function __construct() {
		$this->children = new ArrayCollection();
		$this->posts = new ArrayCollection();
	}

	/**
	 * Returns the category name
	 *
	 * @return string The category name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Sets the category name
	 *
	 * @param string $name The category name
	 * @return void
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * Sets the category parent
	 *
	 * @param Category $parent The category parent
	 * @return void
	 */
	public function setParent(Category $parent) {
		$this->parent = $parent;
	}

	/**
	 * Returns the category children
	 *
	 * @return Collection<\Flowpack\Snippets\Domain\Model\Category> The category children
	 */
	public function getChildren() {
		return clone $this->children;
	}

	/**
	 * Returns the category children
	 *
	 * @return Collection<\Flowpack\Snippets\Domain\Model\Post> The category posts
	 */
	public function getPosts() {
		return clone $this->children;
	}

	/**
	 * Returns the number of posts
	 *
	 * @return integer The number of posts
	 */
	public function getNumberOfPosts() {
		return count($this->posts);
	}

	/**
	 * Returns this tag as a string
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->name;
	}
}