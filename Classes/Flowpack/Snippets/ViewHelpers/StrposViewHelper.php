<?php
namespace Flowpack\Snippets\ViewHelpers;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Use this view helper to check if the value contains a searchstring
 *
 * = Examples =
 *
 *
 * @api
 */
class StrposViewHelper extends AbstractViewHelper {

	
	/**
	 * NOTE: This property has been introduced via code migration to ensure backwards-compatibility.
	 * @see AbstractViewHelper::isOutputEscapingEnabled()
	 * @var boolean
	 */
	protected $escapeOutput = FALSE;

	/**
	 * Check for a Substring
	 *
	 * @param string $needle The searchstring
	 * @param string $value The input value
	 * @return boolean
	 * @api
	 */
	public function render($needle, $value = NULL) {
		if ($value === NULL) {
			$value = $this->renderChildren();
		}

		if (strpos($value, $needle) === FALSE) {
			return FALSE;
		} else {
			return TRUE;
		}
	}
}
