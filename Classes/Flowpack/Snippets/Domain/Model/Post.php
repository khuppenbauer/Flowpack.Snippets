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
use TYPO3\Neos\Domain\Model\User;
use Flowpack\Snippets\Domain\Model\Category;
use Flowpack\ElasticSearch\Annotations as ElasticSearch;

/**
 * A Snippet post
 *
 * @Flow\Entity
 * @ElasticSearch\Indexable("snippets", typeName="post")
 */
class Post {

	/**
	 * The post title
	 *
	 * @var string
	 * @Flow\Validate(type="notEmpty")
	 * @ElasticSearch\Indexable
	 * @ORM\Column(length=255)
	 */
	protected $title;

	/**
	 * The post date
	 *
	 * @var \DateTime
	 * @ElasticSearch\Transform("Date")
	 * @ElasticSearch\Indexable
	 */
	protected $date;

	/**
	 * The post author
	 *
	 * @var User
	 * @ElasticSearch\Transform(type="\Flowpack\Snippets\Indexer\Transform\ObjectAccessTransformer", options={"propertyPath"="name.alias"})
	 * @ElasticSearch\Indexable
	 * @ORM\ManyToOne
	 */
	protected $author;

	/**
	 * The post active state
	 *
	 * @var boolean
	 * @ElasticSearch\Indexable
	 */
	protected $active = FALSE;

	/**
	 * The post description
	 *
	 * @var string
	 * @Flow\Validate(type="StringLength", options={"maximum"=1000})
	 * @ElasticSearch\Indexable
	 * @ORM\Column(length=1000, nullable=true)
	 */
	protected $description;

	/**
	 * The post content
	 *
	 * @var string
	 * @ElasticSearch\Transform("\Flowpack\Snippets\Indexer\Transform\MarkdownReferenceCrawlerTransformer")
	 * @ElasticSearch\Indexable
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $content;

	/**
	 * The post url
	 *
	 * @var string
	 * @Flow\Validate(type="StringLength", options={"maximum"=2000})
	 * @ElasticSearch\Transform("\Flowpack\Snippets\Indexer\Transform\EmbedTransformer")
	 * @ElasticSearch\Indexable
	 * @ORM\Column(length=2000, nullable=true)
	 */
	protected $url;

	/**
	 * The post category
	 *
	 * @var Category
	 * @Flow\Validate(type="notEmpty")
	 * @ElasticSearch\Transform("StringCast")
	 * @ElasticSearch\Indexable
	 * @ORM\ManyToOne(inversedBy="posts")
	 */
	protected $category;

	/**
	 * The post tags
	 *
	 * @var Collection<\Flowpack\Snippets\Domain\Model\Tag>
	 * @Flow\Validate(type="notEmpty")
	 * @ElasticSearch\Transform("CollectionStringCast")
	 * @ElasticSearch\Indexable
	 * @ORM\ManyToMany(inversedBy="posts")
	 */
	protected $tags;

	/**
	 * The post options
	 *
	 * @var array
	 * @ORM\Column(type="json_array")
	 */
	protected $options = array();

	/**
	 * The post views
	 *
	 * @var integer
	 */
	protected $views = 0;

	/**
	 * The post rating
	 *
	 * @var integer
	 */
	protected $rating = 0;

	/**
	 * The embed type
	 *
	 * @var string
	 * @Flow\Transient
	 */
	protected $type;

	/**
	 * The embed image
	 *
	 * @var string
	 * @Flow\Transient
	 */
	protected $image;

	/**
	 * The embed code
	 *
	 * @var string
	 * @Flow\Transient
	 */
	protected $code;

	/**
	 * The embed providerName
	 *
	 * @var string
	 * @Flow\Transient
	 */
	protected $providerName;

	/**
	 * The embed providerUrl
	 *
	 * @var string
	 * @Flow\Transient
	 */
	protected $providerUrl;

	/**
	 * The embed providerIcon
	 *
	 * @var string
	 * @Flow\Transient
	 */
	protected $providerIcon;


	/**
	 * Constructs this post
	 */
	public function __construct() {
		$this->date = new \DateTime();
		$this->tags = new ArrayCollection();
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @param string $title
	 * @return void
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
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
	 * @return boolean
	 */
	public function getActive() {
		return $this->active;
	}

	/**
	 * @return boolean
	 */
	public function isActive() {
		return $this->active;
	}

	/**
	 * @param boolean $active
	 * @return void
	 */
	public function setActive($active) {
		$this->active = $active;
	}

	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @param string $description
	 * @return void
	 */
	public function setDescription($description) {
		$this->description = $description;
	}

	/**
	 * @return string
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * @param string $content
	 * @return void
	 */
	public function setContent($content) {
		$this->content = $content;
	}

	/**
	 * @return string
	 */
	public function getUrl() {
		return $this->url;
	}

	/**
	 * @param string $url
	 * @return void
	 */
	public function setUrl($url) {
		$this->url = $url;
	}

	/**
	 * @return Category
	 */
	public function getCategory() {
		return $this->category;
	}

	/**
	 * @param Category $category
	 * @return void
	 */
	public function setCategory(Category $category = NULL) {
		$this->category = $category;
	}

	/**
	 * @return Collection<\Flowpack\Snippets\Domain\Model\Tag>
	 */
	public function getTags() {
		return clone $this->tags;
	}

	/**
	 * @param Collection<\Flowpack\Snippets\Domain\Model\Tag> $tags
	 * @return void
	 */
	public function setTags(Collection $tags) {
		$this->tags = clone $tags;
	}

	/**
	 * @return void
	 */
	public function removeTags() {
		$this->tags = new ArrayCollection();
	}

	/**
	 * Adds a tag to this post
	 *
	 * @param Tag $tag
	 * @return void
	 */
	public function addTag(Tag $tag) {
		$this->tags->add($tag);
	}

	/**
	 * @return array
	 */
	public function getOptions() {
		return $this->options;
	}

	/**
	 * @param array $options
	 * @return void
	 */
	public function setOptions($options) {
		$this->options = $options;
	}

	/**
	 * @return integer
	 */
	public function getViews() {
		return $this->views;
	}

	/**
	 * @param integer $views
	 * @return void
	 */
	public function setViews($views) {
		$this->views = $views;
	}

	/**
	 * @return integer
	 */
	public function getRating() {
		return $this->rating;
	}

	/**
	 * @param integer $rating
	 * @return void
	 */
	public function setRating($rating) {
		$this->rating = $rating;
	}

	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @param string $type
	 * @return void
	 */
	public function setType($type) {
		$this->type = $type;
	}

	/**
	 * @return string
	 */
	public function getImage() {
		return $this->image;
	}

	/**
	 * @param string $image
	 * @return void
	 */
	public function setImage($image) {
		$this->image = $image;
	}

	/**
	 * @return string
	 */
	public function getCode() {
		return $this->code;
	}

	/**
	 * @param string $code
	 * @return void
	 */
	public function setCode($code) {
		$this->code = $code;
	}


	/**
	 * @return string
	 */
	public function getProviderName() {
		return $this->providerName;
	}

	/**
	 * @param string $providerName
	 * @return void
	 */
	public function setProviderName($providerName) {
		$this->providerName = $providerName;
	}


	/**
	 * @return string
	 */
	public function getProviderUrl() {
		return $this->providerUrl;
	}

	/**
	 * @param string $providerUrl
	 * @return void
	 */
	public function setProviderUrl($providerUrl) {
		$this->providerUrl = $providerUrl;
	}


	/**
	 * @return string
	 */
	public function getProviderIcon() {
		return $this->providerIcon;
	}

	/**
	 * @param string $providerIcon
	 * @return void
	 */
	public function setProviderIcon($providerIcon) {
		$this->providerIcon = $providerIcon;
	}

}