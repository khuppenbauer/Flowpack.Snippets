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
	 * @ORM\ManyToMany(mappedBy="upVotes")
	 */
	protected $upVotes;

	/**
	 * The posts voted down by the User
	 *
	 * @var Collection<\Flowpack\Snippets\Domain\Model\Post>
	 * @ORM\ManyToMany(mappedBy="downVotes")
	 */
	protected $downVotes;

	/**
	 * The posts favorited by the User
	 *
	 * @var Collection<\Flowpack\Snippets\Domain\Model\Post>
	 * @ORM\ManyToMany(mappedBy="favorites")
	 * @ORM\OrderBy({"date" = "DESC"})
	 */
	protected $favorites;

	/**
	 * Constructs this User object
	 *
	 */
	public function __construct() {
		parent::__construct();
		$this->upVotes = new ArrayCollection();
		$this->downVotes = new ArrayCollection();
		$this->favorites = new ArrayCollection();
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

}
