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
class StrtolowerViewHelper extends AbstractViewHelper {

	/**
	 * Check for a Substring
	 *
	 * @param string $value The searchstring
	 * @return string
	 * @api
	 */
	public function render($value = NULL) {
		if ($value === NULL) {
			$value = $this->renderChildren();
		}
		return strtolower($value);
	}
}
