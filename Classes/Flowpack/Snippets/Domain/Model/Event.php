<?php
namespace Flowpack\Snippets\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * A Snippet Event
 *
 * @Flow\ValueObject
 */
class Event {

	/**
	 * The timestamp when the event was created
	 *
	 * @var \DateTime
	 */
	protected $timestamp;

	/**
	 * The user who creates the event
	 *
	 * @var User
	 * @ORM\ManyToOne
	 */
	protected $user;

	/**
	 * The type of the event
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * The post category
	 *
	 * @var Post
	 * @ORM\ManyToOne
	 * @ORM\Column(nullable=true)
	 */
	protected $post;

	/**
	 * Additional optional entity
	 *
	 * @var string
	 * @ORM\Column(nullable=true)
	 */
	protected $entity;

	/**
	 * Identity of the additional optional entity
	 *
	 * @var string
	 * @ORM\Column(nullable=true)
	 */
	protected $entityIdentifier;


	/**
	 * Constructs this event
	 *
	 */
	public function __construct(User $user = NULL, $type, Post $post = NULL, $entity = NULL, $entityIdentifier = NULL) {
		$this->timestamp = new \DateTime();
		$this->user = $user;
		$this->type = $type;
		$this->post = $post;
		$this->entity = $entity;
		$this->entityIdentifier = $entityIdentifier;
	}

	/**
	 * @return \DateTime
	 */
	public function getTimestamp() {
		return $this->timestamp;
	}

	/**
	 * @return User
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @return Post
	 */
	public function getPost() {
		return $this->post;
	}

	/**
	 * @return string
	 */
	public function getEntity() {
		return $this->entity;
	}

	/**
	 * @return string
	 */
	public function getEntityIdentifier() {
		return $this->entityIdentifier;
	}

}