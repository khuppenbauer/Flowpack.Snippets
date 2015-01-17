<?php
namespace Flowpack\Snippets\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use Flowpack\Snippets\Domain\Model\Post;
use TYPO3\Flow\Annotations as Flow;
use Flowpack\Snippets\Domain\Repository\PostRepository;
use TYPO3\Flow\Utility\Files;

/**
 * Class CaptureService
 *
 * @package Flowpack\Snippets\Service
 */
class CaptureService {

	/**
	 * The settings
	 *
	 * @var string
	 */
	protected $settings;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Resource\ResourceManager
	 */
	protected $resourceManager;

	/**
	 * @Flow\Inject
	 * @var PostRepository
	 */
	protected $postRepository;

	/**
	 * @param array $settings
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}

	/**
	 * @param Post $post
	 */
	public function createCapture(Post $post) {
		$url = $post->getUrl();
		if (!empty($url) && $post->getPostType() === 'link') {
			$phantomjsBinaryPathAndFilename = $this->settings['phantomjsBinaryPathAndFilename'];
			$captureScript = $this->settings['captureScript'];
			list($packageKey, $resourcePath) = explode('/', substr($captureScript, 11), 2);
			$targetPathAndFilename = FLOW_PATH_PACKAGES . 'Plugins/' . $packageKey . '/Resources/' . $resourcePath;

			$outputFile = FLOW_PATH_DATA . 'Persistent/Resources/' . $this->persistenceManager->getIdentifierByObject($post) . '.png';
			$command = escapeshellcmd($phantomjsBinaryPathAndFilename . ' ' . $targetPathAndFilename . ' ' . $url . ' ' . $outputFile);
			exec($command, $output, $code);
			if ($code === 0 && is_file($outputFile)) {
				$resource = $this->resourceManager->importResource($outputFile);
				$post->setCapture($resource);
				$this->postRepository->update($post);
			}
			//Files::unlink($outputFile);
		}
	}

}