<?php
namespace Flowpack\Snippets\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\ActionRequest;
use TYPO3\Flow\Mvc\Controller\ControllerContext;
use TYPO3\Flow\Security\Authentication\Controller\AbstractAuthenticationController;

/**
 * Controller that handles the frontend login
 */
class LoginController extends AbstractAuthenticationController {

	/**
	 * @return void
	 */
	public function indexAction() {
	}

	/**
	 * @param ActionRequest $originalRequest The request that was intercepted by the security framework, NULL if there was none
	 * @return void
	 */
	protected function onAuthenticationSuccess(ActionRequest $originalRequest = NULL) {
		$referrer = $this->request->getInternalArgument('__referrer');
		$arguments = $this->request->getArguments();
		$this->redirect($referrer['@action'], $referrer['@controller'], $referrer['@package'], $arguments);
	}

}