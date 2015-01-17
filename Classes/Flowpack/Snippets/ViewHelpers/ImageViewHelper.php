<?php
namespace Flowpack\Snippets\ViewHelpers;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Resource\Resource;
use TYPO3\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;
use Flowpack\Snippets\Service\ImageService;

/**
 * Renders an <img> HTML tag from a given Resource asset instance
 *
 */
class ImageViewHelper extends AbstractTagBasedViewHelper {

	/**
	 * @Flow\Inject
	 * @var ImageService
	 */
	protected $imageService;

	/**
	 * name of the tag to be created by this view helper
	 *
	 * @var string
	 */
	protected $tagName = 'img';


	/**
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerUniversalTagAttributes();
		$this->registerTagAttribute('alt', 'string', 'Specifies an alternate text for an image', TRUE);
	}

	/**
	 * Renders an HTML img tag with a thumbnail image, created from a given resource
	 *
	 * @param string $type Desired maximum height of the image
	 * @param array $options Desired maximum width of the image
	 * @param Resource $resource The asset to be rendered as an image
	 * @param string $uri
	 *
	 * @return string an <img...> html tag
	 */
	public function render($type, $options = array(), Resource $resource = NULL, $uri = NULL) {
		$imageData = $this->imageService->transformImage($resource, $uri, $type, $options);
		$this->tag->addAttributes(array(
			'src' => $imageData
		));

		return $this->tag->render();
	}
}
