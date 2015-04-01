<?php
namespace Flowpack\Snippets\ViewHelpers;

/*                                                                           *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".        *
 *                                                                           *
 *                                                                           */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Configuration\ConfigurationManager;
use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View Helper which returns the configuration from a given path
 *
 * = Example =
 *
 * <code title="Gettings Flow Core Settings">
 * <s:configuration settingPath="TYPO3.Flow.core" />
 * </code>
 * <output>
 * array('context' => '...', 'phpBinaryPathAndFilename' => '...', ...)
 * </output>
 *
 * <code title="Gettings Markdown Settings">
 * <s:configuration settingPath="Flowpack.Snippets" />
 * </code>
 * <output>
 * array('buttons' => '...')
 * </output>
 *
 * @Flow\Scope("prototype")
 */
class ConfigurationViewHelper extends AbstractViewHelper {

	
	/**
	 * NOTE: This property has been introduced via code migration to ensure backwards-compatibility.
	 * @see AbstractViewHelper::isOutputEscapingEnabled()
	 * @var boolean
	 */
	protected $escapeOutput = FALSE;

	/**
	 * @Flow\Inject
	 * @var ConfigurationManager
	 */
	protected $configurationManager;

	/**
	 * @param string $settingPath
	 * @return mixed
	 */
	public function render($settingPath) {
		return $this->configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, $settingPath);
	}

}
