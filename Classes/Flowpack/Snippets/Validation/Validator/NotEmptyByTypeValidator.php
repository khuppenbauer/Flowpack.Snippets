<?php
namespace Flowpack\Snippets\Validation\Validator;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Reflection\ObjectAccess;
use TYPO3\Flow\Validation\Error;
use TYPO3\Flow\Validation\Validator\AbstractValidator;

/**
 * Validator for not empty properties by type
 *
 * @api
 * @Flow\Scope("singleton")
 */
class NotEmptyByTypeValidator extends AbstractValidator {

	/**
	 * Checks if the given value is not empty by type
	 *
	 * @param mixed $value The value that should be validated
	 * @return void
	 * @api
	 */
	protected function isValid($value) {
		switch($value->getPostType()) {
			case 'text':
				$property = 'content';
				break;
			case 'link':
				$property = 'url';
				break;
		}
		$val = ObjectAccess::getProperty($value, $property);
		if (empty($val)) {
			$this->result->forProperty($property)->addError(new Error('This property is required', 1416154237));
		}
	}

}
