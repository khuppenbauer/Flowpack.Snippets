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
use Flowpack\Snippets\Service\UserService;

/**
 * A Snippet tag
 *
 * @Flow\Entity
 */
class Tag {

	/**
	 * The tag name
	 *
	 * @var string
	 * @Flow\Validate(type="StringLength", options={"minimum"=1, "maximum"=255})
	 * @ORM\Column(length=255)
	 */
	protected $name;

	/**
	 * The posts tagged with this tag
	 *
	 * @var Collection<\Flowpack\Snippets\Domain\Model\Post>
	 * @ORM\ManyToMany(mappedBy="tags",cascade={"remove"})
	 */
	protected $posts;

	/**
	 * The users following this tag
	 *
	 * @var Collection<\Flowpack\Snippets\Domain\Model\User>
	 * @ORM\ManyToMany(inversedBy="followedTags")
	 */
	protected $followers;

	/**
	 * @Flow\Inject
	 * @var UserService
	 */
	protected $userService;


	/**
	 * Constructs this tag
	 *
	 * @param string $name
	 */
	public function __construct($name) {
		$this->name = $name;
		$this->posts = new ArrayCollection();
		$this->followers = new ArrayCollection();
	}

	/**
	 * Returns this tag's name
	 *
	 * @return string This tag's name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Returns the tags children
	 *
	 * @return Collection<\Flowpack\Snippets\Domain\Model\Post> The category posts
	 */
	public function getPosts() {
		return clone $this->posts;
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
	 * Add follower to this category
	 *
	 * @return void
	 */
	public function addFollower() {
		$follower = $this->userService->getUser();
		$this->followers->add($follower);
	}

	/**
	 * Removes follower from this category
	 *
	 * @return void
	 */
	public function removeFollower() {
		$follower = $this->userService->getUser();
		$this->followers->removeElement($follower);
	}

	/**
	 * @return boolean
	 */
	public function isFollowed() {
		$isFollowed = FALSE;
		$user = $this->userService->getUser();
		if ($user !== NULL) {
			$followers = clone $this->followers;
			foreach ($followers as $follower) {
				if($follower === $user) {
					$isFollowed = TRUE;
					break;
				}
			}
		}
		return $isFollowed;
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