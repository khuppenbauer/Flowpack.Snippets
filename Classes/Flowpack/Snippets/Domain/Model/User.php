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
	 * Constructs this User object
	 *
	 */
	public function __construct() {
		parent::__construct();
	}



}
