<?php
namespace Flowpack\Snippets\Validation\Validator;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Validation\Validator\AbstractValidator;

/**
 * Validator for url
 *
 * @api
 * @Flow\Scope("singleton")
 */
class UrlValidator extends AbstractValidator {

	/**
	 * Checks if the given value is a valid url.
	 *
	 * @param mixed $value The value that should be validated
	 * @return void
	 * @api
	 */
	protected function isValid($value) {
		if (!is_string($value) || !$this->validUrl($value)) {
			$this->addError('Please specify a valid url.', 1416142850);
		}
	}

	/**
	 * Checking syntax of input url
	 *
	 * @param string $url Input string to evaluate
	 * @return boolean Returns TRUE if the $url (input string) is valid
	 */
	protected function validUrl($url) {
		return (filter_var($url, FILTER_VALIDATE_URL) !== FALSE);
	}
}
