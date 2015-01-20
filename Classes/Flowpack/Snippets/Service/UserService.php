<?php
namespace Flowpack\Snippets\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Security\Context;

/**
 * Class SearchService
 *
 * @package Flowpack\Snippets\Service
 */
class UserService {

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
	 * @return User
	 */
	public function getUser() {
		return $this->securityContext->getPartyByType('Flowpack\Snippets\Domain\Model\User');
	}
}