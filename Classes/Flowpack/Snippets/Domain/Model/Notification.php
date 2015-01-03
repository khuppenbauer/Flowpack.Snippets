<?php
namespace Flowpack\Snippets\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use Flowpack\ElasticSearch\Annotations as ElasticSearch;

/**
 * A Snippet Notification
 *
 * @Flow\Entity
 * @ElasticSearch\Indexable("snippets", typeName="notification")
 */
class Notification {

	/**
	 * The timestamp when the notification was created
	 *
	 * @var \DateTime
	 * @ElasticSearch\Transform("Date", options={"format"="c"})
	 */
	protected $timestamp;

	/**
	 * The user who gets this notification
	 *
	 * @var User
	 * @ORM\ManyToOne
	 * @ElasticSearch\Transform(type="\Flowpack\Snippets\Indexer\Transform\ObjectAccessTransformer", options={"propertyPath"="name.alias"})
	 * @ElasticSearch\Mapping(index="not_analyzed")
	 */
	protected $target;

	/**
	 * The type of the notification
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * The post
	 *
	 * @var Post
	 * @ORM\ManyToOne
	 * @ORM\Column(nullable=true)
	 * @ElasticSearch\Transform(type="\Flowpack\Snippets\Indexer\Transform\ObjectAccessTransformer", options={"propertyPath"="title"})
	 * @ElasticSearch\Mapping(index="not_analyzed")
	 *
	 */
	protected $post;

	/**
	 * The user who sets this notification
	 *
	 * @var User
	 * @ORM\ManyToOne
	 * @ORM\Column(nullable=true)
	 * @ElasticSearch\Transform(type="\Flowpack\Snippets\Indexer\Transform\ObjectAccessTransformer", options={"propertyPath"="name.alias"})
	 * @ElasticSearch\Mapping(index="not_analyzed")
	 */
	protected $source;

	/**
	 * The state of this notification
	 *
	 * @var string
	 */
	protected $state;


	/**
	 * Constructs this event
	 *
	 */
	public function __construct(User $target, $type, Post $post = NULL, User $source = NULL, $state) {
		$this->timestamp = new \DateTime();
		$this->target = $target;
		$this->type = $type;
		$this->post = $post;
		$this->source = $source;
		$this->state = $state;
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
	public function getTarget() {
		return $this->target;
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
	 * @return User
	 */
	public function getSource() {
		return $this->source;
	}

	/**
	 * @return string
	 */
	public function getState() {
		return $this->state;
	}

	/**
	 * @param string $state
	 */
	public function setState($state) {
		$this->state = $state;
	}
}