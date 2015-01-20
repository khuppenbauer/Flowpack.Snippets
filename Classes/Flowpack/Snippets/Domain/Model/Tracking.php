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
 * A post tracking
 *
 * @Flow\Entity
 */
class Tracking {

	/**
	 * @var \DateTime
	 */
	protected $date;

	/**
	 * @var Post
	 * @ORM\ManyToOne(inversedBy="views")
	 */
	protected $post;

	/**
	 * The ipHash
	 *
	 * @var string
	 */
	protected $ipHash;


	/**
	 * Constructs this comment
	 *
	 * @param Post $post
	 * @param $ipHash
	 */
	public function __construct(Post $post, $ipHash) {
		$this->date = new \DateTime();
		$this->post = $post;
		$this->ipHash = $ipHash;
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
	 * Sets the ipHash
	 *
	 * @param string $ipHash
	 * @return void
	 */
	public function setIpHash($ipHash) {
		$this->ipHash = ($ipHash);
	}

	/**
	 * Getter for ipHash
	 *
	 * @return string
	 */
	public function getIpHash() {
		return $this->ipHash;
	}

}

?>