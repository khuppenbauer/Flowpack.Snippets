<?php
namespace Flowpack\Snippets\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Configuration\Exception\InvalidConfigurationException;
use TYPO3\Flow\Object\ObjectManagerInterface;
use TYPO3\Flow\Resource\Resource;
use TYPO3\Flow\Resource\ResourceRepository;
use TYPO3\Flow\Utility\Arrays;
use TYPO3\Media\Exception\ImageFileException;

/**
 * Class ImageService
 *
 * @package Flowpack\Snippets\Service
 */
class ImageService {

	/**
	 * Inset ratio mode: If an image is attempted to get scaled with the size of both edges stated, using this mode will scale it to the lower of both edges.
	 * Consider an image of 320/480 being scaled to 50/50: because aspect ratio wouldn't get hurt, the target image size will become 33/50.
	 */
	const RATIOMODE_INSET = 'inset';

	/**
	 * Outbound ratio mode: If an image is attempted to get scaled with the size of both edges stated, using this mode will scale the image and crop it.
	 * Consider an image of 320/480 being scaled to 50/50: the image will be scaled to height 50, then centered and cropped so the width will also be 50.
	 */
	const RATIOMODE_OUTBOUND = 'outbound';

	/**
	 * @var ObjectManagerInterface
	 * @Flow\Inject
	 */
	protected $objectManager;

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Resource\ResourceManager
	 */
	protected $resourceManager;

	/**
	 * @Flow\Inject
	 * @var ResourceRepository
	 */
	protected $resourceRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Utility\Environment
	 */
	protected $environment;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * @param array $settings
	 * @return void
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}

	/**
	 * @param Resource $resource
	 * @param string $uri
	 * @param string $type
	 * @param array $options
	 * @return mixed
	 */
	public function transformImage(Resource $resource = NULL, $uri = NULL, $type, $options = array()) {
		if (empty($options)) {
			$options = Arrays::getValueByPath($this->settings, 'image.' . $type . '.options');
		}
		$processingInstructions['type'] = $type;
		$processingInstructions['options'] = $options;
		$additionalOptions = array();
		if (!empty($processingInstructions['options']['format'])) {
			$additionalOptions['format'] = $processingInstructions['options']['format'];
			unset($processingInstructions['options']['format']);
		}
		if (!empty($processingInstructions['options']['quality'])) {
			$additionalOptions['quality'] = $processingInstructions['options']['quality'];
			unset($processingInstructions['options']['quality']);
		}
		/** @var \Imagine\Image\ImagineInterface $imagine */
		$imagine = $this->objectManager->get('Imagine\Image\ImagineInterface');
		$additionalOptions = $this->getDefaultOptions($additionalOptions);
		$fileExtension = $additionalOptions['format'];
		if ($resource !== NULL) {
			$filename = $this->persistenceManager->getIdentifierByObject($resource) . '-' . sha1(serialize(array_merge($processingInstructions, $additionalOptions))) . '.' . $fileExtension;
			$resourceUri = $resource->createTemporaryLocalCopy();
			if (!file_exists($resourceUri)) {
				throw new ImageFileException(sprintf('An error occurred while transforming an image: the resource data of the original image does not exist (%s, %s).', $resource->getSha1(), $resourceUri), 1374848224);
			}
			$imagineImage = $imagine->open($resourceUri);
		} else {
			$filename = uniqid() . '.' . $fileExtension;
			$img = file_get_contents($uri);
			$imagineImage = $imagine->load($img);
		}
		$transformedImageTemporaryPathAndFilename = $this->environment->getPathToTemporaryDirectory() . $filename;
		$processedResource = $this->resourceRepository->findOneByFilename($filename);
		if ($processedResource !== NULL) {
			return $this->resourceManager->getPublicPersistentResourceUri($processedResource);
		}

		$imagineImage = $this->applyProcessingInstructions($imagineImage, $processingInstructions);
		$imagineImage->save($transformedImageTemporaryPathAndFilename, $additionalOptions);
		if ($resource === NULL) {
			$resource = $this->resourceManager->importResource($transformedImageTemporaryPathAndFilename);
		}

		$resource = $this->resourceManager->importResource($transformedImageTemporaryPathAndFilename, $resource->getCollectionName());
		if ($resource === FALSE) {
			throw new ImageFileException('An error occurred while importing a generated image file as a resource.', 1413562208);
		}

		unlink($transformedImageTemporaryPathAndFilename);

		$this->persistenceManager->persistAll();
		return $this->resourceManager->getPublicPersistentResourceUri($resource);
	}

	/**
	 * @param array $additionalOptions
	 * @return array
	 * @throws InvalidConfigurationException
	 */
	protected function getDefaultOptions(array $additionalOptions = array()) {
		$defaultOptions = Arrays::getValueByPath($this->settings, 'image.defaultOptions');
		if (!is_array($defaultOptions)) {
			$defaultOptions = array();
		}
		if ($additionalOptions !== array()) {
			$defaultOptions = Arrays::arrayMergeRecursiveOverrule($defaultOptions, $additionalOptions);
		}
		$quality = isset($defaultOptions['quality']) ? (integer)$defaultOptions['quality'] : 90;
		if ($quality < 0 || $quality > 100) {
			throw new InvalidConfigurationException(
					sprintf('Setting "image.defaultOptions.quality" allow only value between 0 and 100, current value: %s', $quality),
					1404982574
			);
		}
		$defaultOptions['jpeg_quality'] = $quality;
		// png_compression_level should be an integer between 0 and 9 and inverse to the quality level given. So quality 100 should result in compression 0.
		$defaultOptions['png_compression_level'] = (9 - ceil($quality * 9 / 100));
		return $defaultOptions;
	}

	/**
	 * @param \Imagine\Image\ImageInterface $image
	 * @param array $processingInstructions
	 * @return \Imagine\Image\ImageInterface
	 * @throws \InvalidArgumentException
	 */
	protected function applyProcessingInstructions(\Imagine\Image\ImageInterface $image, array $processingInstructions) {
		$commandName = $processingInstructions['type'];
		$commandMethodName = sprintf('%sCommand', $commandName);
		if (!is_callable(array($this, $commandMethodName))) {
			throw new \InvalidArgumentException('Invalid command "' . $commandName . '"', 1316613563);
		}
		$image = call_user_func(array($this, $commandMethodName), $image, $processingInstructions['options']);
		return $image;
	}

	/**
	 * @param \Imagine\Image\ImageInterface $image
	 * @param array $commandOptions array('size' => ('width' => 123, 'height => 456), 'mode' => 'outbound')
	 * @return \Imagine\Image\ImageInterface
	 */
	protected function thumbnailCommand(\Imagine\Image\ImageInterface $image, array $commandOptions) {
		if (!isset($commandOptions['size'])) {
			throw new \InvalidArgumentException('The thumbnailCommand needs a "size" option.', 1393510202);
		}
		$dimensions = $this->parseBox($commandOptions['size']);
		if (isset($commandOptions['mode']) && $commandOptions['mode'] === self::RATIOMODE_OUTBOUND) {
			$mode = \Imagine\Image\ManipulatorInterface::THUMBNAIL_OUTBOUND;
		} else {
			$mode = \Imagine\Image\ManipulatorInterface::THUMBNAIL_INSET;
		}
		if (isset($commandOptions['start']) && $mode === \Imagine\Image\ManipulatorInterface::THUMBNAIL_OUTBOUND) {
			$startPoint = $this->parsePoint($commandOptions['start']);
			$size = $image->getSize();
			$command = 'widen';
			$parameter = $commandOptions['size'][0];
			return $image->resize(call_user_func(array($size, $command), $parameter))->crop($startPoint, $dimensions);
		} else {
			return $image->thumbnail($dimensions, $mode);
		}
	}

	/**
	 * @param \Imagine\Image\ImageInterface $image
	 * @param array $commandOptions array('size' => ('width' => 123, 'height => 456))
	 * @return \Imagine\Image\ImageInterface
	 */
	protected function resizeCommand(\Imagine\Image\ImageInterface $image, array $commandOptions) {
		if (!isset($commandOptions['size'])) {
			throw new \InvalidArgumentException('The resizeCommand needs a "size" option.', 1393510215);
		}
		$dimensions = $this->parseBox($commandOptions['size']);
		return $image->resize($dimensions);
	}

	/**
	 * @param \Imagine\Image\ImageInterface $image
	 * @param array $commandOptions array('widen' => 300) array('heighten' => 300) array('increase' => 10) array('scale' => 2.5)
	 * @return \Imagine\Image\ImageInterface
	 */
	protected function relativeResizeCommand(\Imagine\Image\ImageInterface $image, array $commandOptions) {
		$methodArray = array_keys($commandOptions);
		$method = $methodArray[0];
		if (!in_array($method, array('heighten', 'increase', 'scale', 'widen'))) {
			throw new \InvalidArgumentException('The relativeResize option must be of type "heighten, widen, scale or increase".', 1413761472);
		}
		$parameter = $commandOptions[$method];
		return $image->resize(call_user_func(array($image->getSize(), $method), $parameter));
	}

	/**
	 * @param \Imagine\Image\ImageInterface $image
	 * @param array $commandOptions array('start' => array('x' => 123, 'y' => 456), 'size' => array('width' => 123, 'height => 456))
	 * @return \Imagine\Image\ImageInterface
	 */
	protected function cropCommand(\Imagine\Image\ImageInterface $image, array $commandOptions) {
		if (!isset($commandOptions['start'])) {
			throw new \InvalidArgumentException('The cropCommand needs a "start" option.', 1393510229);
		}
		if (!isset($commandOptions['size'])) {
			throw new \InvalidArgumentException('The cropCommand needs a "size" option.', 1393510231);
		}
		$startPoint = $this->parsePoint($commandOptions['start']);
		$dimensions = $this->parseBox($commandOptions['size']);
		return $image->crop($startPoint, $dimensions);
	}

	/**
	 * @param array $coordinates
	 * @return \Imagine\Image\Point
	 */
	protected function parsePoint($coordinates) {
		if (self::is_assoc($coordinates)) {
			$x = $coordinates['x'];
			$y = $coordinates['y'];
		} else {
			list($x, $y) = $coordinates;
		}
		return $this->objectManager->get('Imagine\Image\Point', $x, $y);
	}

	/**
	 * @param array $dimensions
	 * @return \Imagine\Image\Box
	 */
	protected function parseBox($dimensions) {
		if (self::is_assoc($dimensions)) {
			$width = $dimensions['width'];
			$height = $dimensions['height'];
		} else {
			list($width, $height) = $dimensions;
		}
		return $this->objectManager->get('Imagine\Image\Box', $width, $height);
	}

	/**
	 * @param array $array
	 * @return boolean
	 */
	static function is_assoc($array) {
		return (bool)count(array_filter(array_keys($array), 'is_string'));
	}

}