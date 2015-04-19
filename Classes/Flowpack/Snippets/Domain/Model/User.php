<?php
namespace Flowpack\Snippets\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use TYPO3\Party\Domain\Model\Person;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;
use Flowpack\Snippets\Service\UserService;

/**
 * Domain Model of a User
 *
 * @Flow\Entity
 * @Flow\Scope("prototype")
 */
class User extends Person {

	/**
	 * The posts voted up by the User
	 *
	 * @var Collection<\Flowpack\Snippets\Domain\Model\Post>
	 * @ORM\ManyToMany(mappedBy="upVotes",cascade={"remove"})
	 */
	protected $upVotes;

	/**
	 * The posts voted down by the User
	 *
	 * @var Collection<\Flowpack\Snippets\Domain\Model\Post>
	 * @ORM\ManyToMany(mappedBy="downVotes",cascade={"remove"})
	 */
	protected $downVotes;

	/**
	 * The posts favorited by the User
	 *
	 * @var Collection<\Flowpack\Snippets\Domain\Model\Post>
	 * @ORM\ManyToMany(mappedBy="favorites",cascade={"remove"})
	 * @ORM\OrderBy({"date" = "DESC"})
	 */
	protected $favorites;

	/**
	 * The posts written by the User
	 *
	 * @var Collection<\Flowpack\Snippets\Domain\Model\Post>
	 * @ORM\OneToMany(mappedBy="author")
	 * @ORM\OrderBy({"date" = "DESC"})
	 */
	protected $posts;

	/**
	 * The category followed by the User
	 *
	 * @var Collection<\Flowpack\Snippets\Domain\Model\Category>
	 * @ORM\ManyToMany(mappedBy="followers")
	 */
	protected $followedCategories;

	/**
	 * The category followed by the User
	 *
	 * @var Collection<\Flowpack\Snippets\Domain\Model\Tag>
	 * @ORM\ManyToMany(mappedBy="followers")
	 */
	protected $followedTags;

	/**
	 * The category followed by the User
	 *
	 * @var Collection<\Flowpack\Snippets\Domain\Model\User>
	 * @ORM\ManyToMany(mappedBy="followers")
	 */
	protected $followedUsers;

	/**
	 * The users following this user
	 *
	 * @var Collection<\Flowpack\Snippets\Domain\Model\User>
	 * @ORM\ManyToMany(inversedBy="followedUsers")
	 * @ORM\JoinTable(inverseJoinColumns={@ORM\JoinColumn(name="related_user")})
	 */
	protected $followers;

	/**
	 * @Flow\Inject
	 * @var UserService
	 */
	protected $userService;


	/**
	 * Constructs this User object
	 *
	 */
	public function __construct() {
		parent::__construct();
		$this->upVotes = new ArrayCollection();
		$this->downVotes = new ArrayCollection();
		$this->favorites = new ArrayCollection();
		$this->posts = new ArrayCollection();
		$this->followedCategories = new ArrayCollection();
		$this->followedTags = new ArrayCollection();
		$this->followedUsers = new ArrayCollection();
		$this->followers = new ArrayCollection();
	}

	/**
	 * @return Collection<\Flowpack\Snippets\Domain\Model\Post>
	 */
	public function getFavorites() {
		return clone $this->favorites;
	}

	/**
	 * @param Post $post
	 * @return boolean
	 */
	public function isFavorite(Post $post) {
		$isFavorite = FALSE;
		$favorites = clone $this->favorites;
		foreach ($favorites as $favorite) {
			if($favorite === $post) {
				$isFavorite = TRUE;
				break;
			}
		}
		return $isFavorite;
	}

	/**
	 * Returns the Users posts
	 *
	 * @return Collection<\Flowpack\Snippets\Domain\Model\Post> The category posts
	 */
	public function getPosts() {
		return clone $this->posts;
	}

	/**
	 * Returns the number of the User posts
	 *
	 * @return integer The number of posts
	 */
	public function getNumberOfPosts() {
		return count($this->posts);
	}

	/**
	 * Add follower to this user
	 *
	 * @return void
	 */
	public function addFollower() {
		$follower = $this->userService->getUser();
		$this->followers->add($follower);
	}

	/**
	 * Removes follower from this user
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
	 * Returns the number of the User posts
	 *
	 * @return integer The number of posts
	 */
	public function getNumberOfFollowers() {
		return count($this->followers);
	}

	/**
	 * Returns the number of the User posts
	 *
	 * @return integer The number of posts
	 */
	public function getNumberOfFollowedUsers() {
		return count($this->followedUsers);
	}

	/**
	 * @return Collection<\Flowpack\Snippets\Domain\Model\User>
	 */
	public function getFollowedUsers() {
		return clone $this->followedUsers;
	}

	/**
	 * @return Collection<\Flowpack\Snippets\Domain\Model\Category>
	 */
	public function getFollowedCategories() {
		return clone $this->followedCategories;
	}

	/**
	 * @return Collection<\Flowpack\Snippets\Domain\Model\Tag>
	 */
	public function getFollowedTags() {
		return clone $this->followedTags;
	}

	/**
	 * Returns this tag as a string
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->getName()->getAlias();
	}
}
