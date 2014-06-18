<?php
namespace Flowpack\Snippets\ViewHelpers;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * ViewHelper that renders a section or a specified partial
 *
 * == Examples ==
 *
 */
class TagsViewHelper extends AbstractViewHelper {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * @param mixed $options
	 * @return string
	 */
	public function render($options = NULL) {
		if ($options === NULL) {
			$options = $this->renderChildren();
		}
		if (empty($options)) {
			return '';
		}
		$optionArray = array();
		foreach ($options as $option) {
			$optionArray[] = $this->persistenceManager->getIdentifierByObject($option);
		}
		return implode(',', $optionArray);

	}

}
