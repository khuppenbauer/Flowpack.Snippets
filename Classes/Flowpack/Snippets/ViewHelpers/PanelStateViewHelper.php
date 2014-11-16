<?php
namespace Flowpack\Snippets\ViewHelpers;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Reflection\ObjectAccess;
use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\Flow\Mvc\ActionRequest;

/**
 * ViewHelper that returns the panel state by the submitted arguments
 *
 * == Examples ==
 *
 */
class PanelStateViewHelper extends AbstractViewHelper {

	/**
	 * Shortcut for retrieving the request from the controller context
	 *
	 * @return ActionRequest
	 */
	protected function getRequest() {
		return $this->controllerContext->getRequest();
	}

	/**
	 * @param string $property
	 * @param string $value
	 * @return string
	 */
	public function render($property, $value) {
		$arguments = $this->getRequest()->getInternalArgument('__submittedArguments');
		if (ObjectAccess::getPropertyPath($arguments, $property) === $value) {
			return 'active';
		} else {
			return '';
		}

	}

}
