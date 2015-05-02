<?php
namespace Flowpack\Snippets\ViewHelpers\Form;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\Flow\Reflection\ObjectAccess;
use TYPO3\Flow\Mvc\ActionRequest;

/**
 * Sets the propertyPath of the requested argument in the templateVariableContainer
 *
 * <f:form.property name="myVar" property="myProperty" default="defaultValue" />
 *
 * @api
 */
class PropertyViewHelper extends AbstractViewHelper {

	/**
	 * @var boolean
	 */
	protected $escapeOutput = FALSE;

	/**
	 * Shortcut for retrieving the request from the controller context
	 *
	 * @return ActionRequest
	 */
	protected function getRequest() {
		return $this->controllerContext->getRequest();
	}

	/**
	 * Sets the propertyPath of the requested argument in the templateVariableContainer
	 * If there is no argument yet, the default value is set
	 *
	 * @param string $name
	 * @param string $property
	 * @param string $default
	 * @return void
	 * @api
	 */
	public function render($name, $property, $default = NULL) {
		$argument = $this->getRequest()->getInternalArgument('__submittedArguments');
		$value = ObjectAccess::getPropertyPath($argument, $property);
		if ($value === NULL && $default !== NULL) {
			$value = $default;
		}
		$this->templateVariableContainer->add($name, $value);
		return NULL;
	}
}
