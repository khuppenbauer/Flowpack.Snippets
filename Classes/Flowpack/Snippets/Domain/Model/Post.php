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
use Flowpack\ElasticSearch\Annotations as ElasticSearch;
use Flowpack\Snippets\Domain\Model\User;
use TYPO3\Flow\Security\Context;

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
	 * @ElasticSearch\Transform("Date", options={"format"="c"})
	 * @ElasticSearch\Indexable
	 * @ElasticSearch\Mapping(index="no")
	 */
	protected $date;

	/**
	 * The post author
	 *
	 * @var User
	 * @ElasticSearch\Transform(type="\Flowpack\Snippets\Indexer\Transform\ObjectAccessTransformer", options={"propertyPath"="name.alias"})
	 * @ElasticSearch\Indexable
	 * @ElasticSearch\Mapping(index="not_analyzed")
	 * @ORM\ManyToOne
	 */
	protected $author;

	/**
	 * The post active state
	 *
	 * @var boolean
	 * @ElasticSearch\Indexable
	 */
	protected $active = TRUE;

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
	 * @Flow\Validate(type="\Flowpack\Snippets\Validation\Validator\UrlValidator")
	 * @ElasticSearch\Transform("\Flowpack\Snippets\Indexer\Transform\EmbedTransformer")
	 * @ElasticSearch\Indexable
	 * @ElasticSearch\Mapping(index="not_analyzed")
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
	 * @ElasticSearch\Mapping(analyzer="string_lowercase", fields={@Elasticsearch\Mapping(index_name="raw", type="string", index="not_analyzed")})
	 * @ORM\ManyToOne(inversedBy="posts")
	 */
	protected $category;

	/**
	 * The post tags
	 *
	 * @var Collection<\Flowpack\Snippets\Domain\Model\Tag>
	 * @ElasticSearch\Transform("CollectionStringCast")
	 * @ElasticSearch\Indexable
	 * @ElasticSearch\Mapping(analyzer="string_lowercase", fields={@Elasticsearch\Mapping(index_name="raw", type="string", index="not_analyzed")})
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
	 * The post type // text or link
	 *
	 * @var string
	 */
	protected $postType;

	/**
	 * The post views
	 *
	 * @var Collection<\Flowpack\Snippets\Domain\Model\Tracking>
	 * @ORM\OneToMany(mappedBy="post", cascade={"persist"})
	 */
	protected $views;

	/**
	 * The post numberOf unique views
	 *
	 * @var integer
	 * @Flow\Transient
	 * @ElasticSearch\Indexable
	 */
	protected $numberOfViews;

	/**
	 * The post upVotes
	 *
	 * @var Collection<\Flowpack\Snippets\Domain\Model\User>
	 * @ORM\ManyToMany(inversedBy="upVotes")
	 */
	protected $upVotes;

	/**
	 * The post downVotes
	 *
	 * @var Collection<\Flowpack\Snippets\Domain\Model\User>
	 * @ORM\ManyToMany(inversedBy="downVotes")
	 */
	protected $downVotes;

	/**
	 * The post numberOf Votes
	 *
	 * @var integer
	 * @Flow\Transient
	 * @ElasticSearch\Indexable
	 */
	protected $numberOfVotes;

	/**
	 * The post favorites
	 *
	 * @var Collection<\Flowpack\Snippets\Domain\Model\User>
	 * @ORM\ManyToMany(inversedBy="favorites")
	 */
	protected $favorites;

	/**
	 * The post numberOf Votes
	 *
	 * @var integer
	 * @Flow\Transient
	 * @ElasticSearch\Indexable
	 */
	protected $numberOfFavorites;

	/**
	 * @var Collection<\Flowpack\Snippets\Domain\Model\Comment>
	 * @ORM\OneToMany(mappedBy="post")
	 * @ORM\OrderBy({"date" = "DESC"})
	 */
	protected $comments;

	/**
	 * The embed type
	 *
	 * @var string
	 * @Flow\Transient
	 * @ElasticSearch\Indexable
	 * @ElasticSearch\Mapping(analyzer="string_lowercase", fields={@Elasticsearch\Mapping(index_name="raw", type="string", index="not_analyzed")})
	 */
	protected $type;

	/**
	 * The embed image
	 *
	 * @var string
	 * @Flow\Transient
	 * @ElasticSearch\Indexable
	 * @ElasticSearch\Mapping(index="no")
	 */
	protected $image;

	/**
	 * The embed code
	 *
	 * @var string
	 * @Flow\Transient
	 * @ElasticSearch\Indexable
	 * @ElasticSearch\Mapping(index="no")
	 */
	protected $code;

	/**
	 * The embed providerName
	 *
	 * @var string
	 * @Flow\Transient
	 * @ElasticSearch\Indexable
	 * @ElasticSearch\Mapping(analyzer="string_lowercase", fields={@Elasticsearch\Mapping(index_name="raw", type="string", index="not_analyzed")})
	 */
	protected $providerName;

	/**
	 * The embed providerUrl
	 *
	 * @var string
	 * @Flow\Transient
	 * @ElasticSearch\Indexable
	 * @ElasticSearch\Mapping(index="no")
	 */
	protected $providerUrl;

	/**
	 * The embed providerIcon
	 *
	 * @var string
	 * @Flow\Transient
	 * @ElasticSearch\Indexable
	 * @ElasticSearch\Mapping(index="no")
	 */
	protected $providerIcon;

	/**
	 * The embed providerIcon
	 *
	 * @var User
	 * @Flow\Transient
	 */
	protected $user;

	/**
	 * @var Context
	 */
	protected $securityContext;

	/**
	 * Injects the Security Context
	 *
	 * @param Context $securityContext
	 * @return void
	 */
	public function injectSecurityContext(Context $securityContext) {
		$this->securityContext = $securityContext;
	}

	/**
	 * Constructs this post
	 */
	public function __construct() {
		$this->date = new \DateTime();
		$this->tags = new ArrayCollection();
		$this->upVotes = new ArrayCollection();
		$this->downVotes = new ArrayCollection();
		$this->favorites = new ArrayCollection();
		$this->comments = new ArrayCollection();
		$this->views = new ArrayCollection();
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
	 * @return string
	 */
	public function getPostType() {
		return $this->postType;
	}

	/**
	 * @param string $postType
	 * @return void
	 */
	public function setPostType($postType) {
		$this->postType = $postType;
	}

	/**
	 * @return Collection<\Flowpack\Snippets\Domain\Model\User>
	 */
	public function getUpVotes() {
		return clone $this->upVotes;
	}

	/**
	 * Returns the number of upVotes
	 *
	 * @return integer The number of upVotes
	 */
	public function getNumberOfUpVotes() {
		return count($this->upVotes);
	}

	/**
	 * @param Collection<\Flowpack\Snippets\Domain\Model\User> $upVotes
	 * @return void
	 */
	public function setUpVotes(Collection $upVotes) {
		$this->upVotes = clone $upVotes;
	}

	/**
	 * Adds an upVote to this post
	 *
	 * @return void
	 */
	public function addUpVote() {
		$upVote = $this->getUser();
		$this->upVotes->add($upVote);
	}

	/**
	 * Removes an upVote from this post
	 *
	 * @return void
	 */
	public function removeUpVote() {
		$upVote = $this->getUser();
		$this->upVotes->removeElement($upVote);
	}

	/**
	 * @return boolean
	 */
	public function hasUpVote() {
		$hasUpVote = FALSE;
		$user = $this->getUser();
		if ($user !== NULL) {
			$upVotes = clone $this->upVotes;
			foreach ($upVotes as $upVote) {
				if($upVote === $user) {
					$hasUpVote = TRUE;
					break;
				}
			}
		}
		return $hasUpVote;
	}

	/**
	 * @return Collection<\Flowpack\Snippets\Domain\Model\User>
	 */
	public function getDownVotes() {
		return clone $this->downVotes;
	}

	/**
	 * Returns the number of downVotes
	 *
	 * @return integer The number of downVotes
	 */
	public function getNumberOfDownVotes() {
		return count($this->downVotes);
	}

	/**
	 * @param Collection<\Flowpack\Snippets\Domain\Model\User> $downVotes
	 * @return void
	 */
	public function setDownVotes(Collection $downVotes) {
		$this->downVotes = clone $downVotes;
	}

	/**
	 * Adds a downVote
	 *
	 * @return void
	 */
	public function addDownVote() {
		$downVote = $this->getUser();
		$this->downVotes->add($downVote);
	}

	/**
	 * Removes a downVote
	 *
	 * @return void
	 */
	public function removeDownVote() {
		$downVote = $this->getUser();
		$this->downVotes->removeElement($downVote);
	}

	/**
	 * @return boolean
	 */
	public function hasDownVote() {
		$hasDownVote = FALSE;
		$user = $this->getUser();
		if ($user !== NULL) {
			$downVotes = clone $this->downVotes;
			foreach ($downVotes as $downVote) {
				if($downVote === $user) {
					$hasDownVote = TRUE;
					break;
				}
			}
		}
		return $hasDownVote;
	}

	/**
	 * Returns the number of Votes
	 *
	 * @return integer The number of Votes
	 */
	public function getNumberOfVotes() {
		return count($this->upVotes) - count($this->downVotes);
	}

	/**
	 * @return Collection<\Flowpack\Snippets\Domain\Model\User>
	 */
	public function getFavorites() {
		return clone $this->favorites;
	}

	/**
	 * @param Collection<\Flowpack\Snippets\Domain\Model\User> $favorites
	 * @return void
	 */
	public function setFavorites(Collection $favorites) {
		$this->favorites = clone $favorites;
	}

	/**
	 * Add favorite to this post
	 *
	 * @return void
	 */
	public function addFavorite() {
		$favorite = $this->getUser();
		$this->favorites->add($favorite);
	}

	/**
	 * Removes favorite from this post
	 *
	 * @return void
	 */
	public function removeFavorite() {
		$favorite = $this->getUser();
		$this->favorites->removeElement($favorite);
	}

	/**
	 * @return boolean
	 */
	public function isFavorite() {
		$isFavorite = FALSE;
		$user = $this->getUser();
		if ($user !== NULL) {
			$favorites = clone $this->favorites;
			foreach ($favorites as $favorite) {
				if($favorite === $user) {
					$isFavorite = TRUE;
					break;
				}
			}
		}
		return $isFavorite;
	}

	/**
	 * Returns the number of Votes
	 *
	 * @return integer The number of Votes
	 */
	public function getNumberOfFavorites() {
		return count($this->favorites);
	}

	/**
	 * Adds a tracking to this post
	 *
	 * @param Tracking $tracking
	 * @return void
	 */
	public function addView(Tracking $tracking) {
		$tracking->setPost($this);
		$this->views->add($tracking);
	}

	/**
	 * Returns the number of views
	 *
	 * @return integer The number of views
	 */
	public function getNumberOfViews() {
		return count($this->views);
	}

	/**
	 * Adds a comment to this post
	 *
	 * @param Comment $comment
	 * @return void
	 */
	public function addComment(Comment $comment) {
		$comment->setPost($this);
		$this->comments->add($comment);
	}

	/**
	 * Removes a comment from this post
	 *
	 * @param Comment $comment
	 * @return void
	 */
	public function removeComment(Comment $comment) {
		$this->comments->removeElement($comment);
	}

	/**
	 * Returns the comments to this post
	 *
	 * @return \Doctrine\Common\Collections\Collection<\Flowpack\Snippets\Domain\Model\Comment>
	 */
	public function getComments() {
		return $this->comments;
	}

	/**
	 * Returns the number of comments
	 *
	 * @return integer The number of comments
	 */
	public function getNumberOfComments() {
		return count($this->comments);
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

	/**
	 * @return User
	 */
	public function getUser() {
		return $this->securityContext->getPartyByType('Flowpack\Snippets\Domain\Model\User');
	}


}