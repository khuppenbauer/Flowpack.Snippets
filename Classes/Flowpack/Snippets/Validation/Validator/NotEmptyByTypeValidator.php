<?php
namespace Flowpack\Snippets\Validation\Validator;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Reflection\ObjectAccess;
use TYPO3\Flow\Utility\Arrays;
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
	 * The settings
	 *
	 * @var string
	 */
	protected $settings;

	/**
	 * @param array $settings
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}

	/**
	 * Checks if the given value is not empty by type
	 *
	 * @param mixed $value The value that should be validated
	 * @return void
	 * @api
	 */
	protected function isValid($value) {
		$mandatory = Arrays::getValueByPath($this->settings['mandatory'], $value->getPostType());
		if (!empty($mandatory)) {
			foreach ($mandatory as $property) {
				$val = ObjectAccess::getProperty($value, $property);
				if (empty($val)) {
					$this->result->forProperty($property)->addError(new Error('This property is required', 1416154237));
				}
			}
		}
	}

}
