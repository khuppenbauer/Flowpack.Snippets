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
 * A Snippet tag
 *
 * @Flow\ValueObject
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
	 * @ORM\ManyToMany(mappedBy="tags")
	 */
	protected $posts;

	/**
	 * Constructs this tag
	 *
	 * @param string $name
	 */
	public function __construct($name) {
		$this->name = $name;
		$this->posts = new ArrayCollection();
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
	 * Returns this tag as a string
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->name;
	}

}