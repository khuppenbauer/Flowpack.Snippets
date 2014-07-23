<?php
namespace Flowpack\Snippets\ViewHelpers;

/*                                                                           *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".        *
 *                                                                           *
 *                                                                           */
use Flowpack\Snippets\Domain\Model\User;

/**
 * This view helper checks if the user is author of this post.
 *
 * @api
 */
class IfAuthorViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractConditionViewHelper {

	/**
	 * @var \TYPO3\Flow\Security\Context
	 */
	protected $securityContext;

	/**
	 * Injects the security context
	 *
	 * @param \TYPO3\Flow\Security\Context $securityContext The security context
	 * @return void
	 */
	public function injectSecurityContext(\TYPO3\Flow\Security\Context $securityContext) {
		$this->securityContext = $securityContext;
	}

	/**
	 * renders <f:then> child if the author is the logged party,
	 * otherwise renders <f:else> child.
	 *
	 * @param User $post The author of the post
	 * @return string the rendered string
	 * @api
	 */
	public function render(User $user) {
		$account = $this->securityContext->getAccount();
		if ($account !== NULL && $account->getParty() === $user) {
			return $this->renderThenChild();
		} else {
			return $this->renderElseChild();
		}
	}

}


?>