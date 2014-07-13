<?php
namespace Flowpack\Snippets\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;
use Flowpack\Snippets\Domain\Model\Post;
use Flowpack\Snippets\Domain\Model\User;

/**
 * A post comment
 *
 * @Flow\Entity
 */
class Comment {

	/**
	 * @var \DateTime
	 */
	protected $date;

	/**
	 * @var Post
	 * @ORM\ManyToOne(inversedBy="comments")
	 */
	protected $post;

	/**
	 * The post author
	 *
	 * @var User
	 * @ORM\ManyToOne
	 */
	protected $author;

	/**
	 * @var string
	 * @Flow\Validate(type="NotEmpty")
	 * @ORM\Column(type="text")
	 */
	protected $content;

	/**
	 * Constructs this comment
	 *
	 */
	public function __construct() {
		$this->date = new \DateTime();
	}

	/**
	 * @param Post $post
	 * @return void
	 */
	public function setPost(Post $post) {
		$this->post = $post;
	}

	/**
	 * Setter for date
	 *
	 * @param \DateTime $date
	 * @return void
	 */
	public function setDate(\DateTime $date) {
		$this->date = $date;
	}

	/**
	 * Getter for date
	 *
	 * @return \DateTime
	 */
	public function getDate() {
		return $this->date;
	}

	/**
	 * @return User
	 */
	public function getAuthor() {
		return $this->author;
	}

	/**
	 * @param User $author
	 * @return void
	 */
	public function setAuthor(User $author) {
		$this->author = $author;
	}

	/**
	 * Sets the content for this comment
	 *
	 * @param string $content
	 * @return void
	 */
	public function setContent($content) {
		$this->content = $content;
	}

	/**
	 * Getter for content
	 *
	 * @return string
	 */
	public function getContent() {
		return $this->content;
	}

}

?>