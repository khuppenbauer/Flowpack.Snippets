<?php
namespace Flowpack\Snippets\ViewHelpers;

/*                                                                           *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".        *
 *                                                                           *
 *                                                                           */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View Helper which converts a text from markdown to html
 *
 * = Example =
 *
 * <code title="Example">
 * <markdown:parsedown>**strong text**</markdown:parsedown>
 * </code>
 * <output>
 * <strong>strong text</strong>
 * </output>
 *
 * @Flow\Scope("prototype")
 */
class ParsedownViewHelper extends AbstractViewHelper {

	
	/**
	 * NOTE: This property has been introduced via code migration to ensure backwards-compatibility.
	 * @see AbstractViewHelper::isOutputEscapingEnabled()
	 * @var boolean
	 */
	protected $escapeOutput = FALSE;

	/**
	 * @param string $text
	 * @return string
	 */
	public function render($text = NULL) {
		if ($text === NULL) {
			$text = $this->renderChildren();
		}
		$parsedown = new \Parsedown();
		return $parsedown->text($text);
	}

}
