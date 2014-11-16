<?php
namespace Flowpack\Snippets\ViewHelpers\Form;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper;

/**
 * View Helper which creates a text field which is used as multiselect field for the select2 jquery plugin
 *
 * = Examples =
 *
 * <code title="Example">
 * <snippets:form.tags name="myTextBox" value="default value" />
 * </code>
 * <output>
 * <input type="text" name="myTextBox" value="default value" />
 * </output>
 *
 * @api
 */
class MultiselectViewHelper extends AbstractFormFieldViewHelper {

	/**
	 * @var string
	 */
	protected $tagName = 'input';

	/**
	 * Initialize the arguments.
	 *
	 * @return void
	 * @api
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerTagAttribute('disabled', 'string', 'Specifies that the input element should be disabled when the page loads');
		$this->registerTagAttribute('maxlength', 'int', 'The maxlength attribute of the input field (will not be validated)');
		$this->registerTagAttribute('readonly', 'string', 'The readonly attribute of the input field');
		$this->registerTagAttribute('size', 'int', 'The size of the input field');
		$this->registerTagAttribute('placeholder', 'string', 'The placeholder of the input field');
		$this->registerTagAttribute('autofocus', 'string', 'Specifies that a input field should automatically get focus when the page loads');
		$this->registerArgument('errorClass', 'string', 'CSS class to set if there are errors for this view helper', FALSE, 'f3-form-error');
		$this->registerUniversalTagAttributes();
	}

	/**
	 * Renders the textfield.
	 *
	 * @param boolean $required If the field is required or not
	 * @param string $type The field type, e.g. "text", "email", "url" etc.
	 * @return string
	 * @api
	 */
	public function render($required = FALSE, $type = 'text') {
		$name = $this->getName();
		$this->registerFieldNameForFormTokenGeneration($name);

		$this->tag->addAttribute('type', $type);
		$this->tag->addAttribute('name', $name);

		$value = $this->getValue();
		if (!empty($value) && count($value) > 0) {
			$value = implode(',', $value);
			$this->tag->addAttribute('value', $value);
		}

		if ($required === TRUE) {
			$this->tag->addAttribute('required', 'required');
		}

		$this->setErrorClassAttribute();

		return $this->tag->render();
	}
}
