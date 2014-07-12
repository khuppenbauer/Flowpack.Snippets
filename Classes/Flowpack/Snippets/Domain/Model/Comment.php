<?php
namespace Flowpack\Snippets\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;
use Flowpack\Snippets\Domain\Model\Post;

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
	 * @var string
	 * @Flow\Validate(type="Text")
	 * @Flow\Validate(type="StringLength", options={ "minimum"=3, "maximum"=80 })
	 * @ORM\Column(length=80)
	 */
	protected $author;

	/**
	 * @var string
	 * @Flow\Validate(type="EmailAddress")
	 */
	protected $emailAddress;

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
	 * Setter for date
	 *
	 * @param \DateTime $date
	 * @return void
	 */
	public function setDate(\DateTime $date) {
		$this->date = $date;
	}

	/**
	 * @param Post $post
	 * @return void
	 */
	public function setPost(Post $post) {
		$this->post = $post;
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
	 * Sets the author for this comment
	 *
	 * @param string $author
	 * @return void
	 */
	public function setAuthor($author) {
		$this->author = $author;
	}

	/**
	 * Getter for author
	 *
	 * @return string
	 */
	public function getAuthor() {
		return $this->author;
	}

	/**
	 * Sets the authors email address for this comment
	 *
	 * @param string $emailAddress email address of the author
	 * @return void
	 */
	public function setEmailAddress($emailAddress) {
		$this->emailAddress = $emailAddress;
	}

	/**
	 * Getter for authors email address
	 *
	 * @return string
	 */
	public function getEmailAddress() {
		return $this->emailAddress;
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