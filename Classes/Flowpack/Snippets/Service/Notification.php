<?php
namespace Flowpack\Snippets\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\I18n\Translator;
use Flowpack\Snippets\Domain\Model\Comment;
use Flowpack\Snippets\Domain\Model\Post;
use TYPO3\Fluid\View\StandaloneView;
use TYPO3\SwiftMailer\Message;

/**
 * A notification service
 *
 */
class Notification {

	/**
	 * @Flow\Inject
	 * @var Translator
	 */
	protected $translator;

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @param array $settings
	 * @return void
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}

	/**
	 * @param Comment $comment
	 * @param Post $post
	 * @return void
	 */
	public function sendNewCommentNotification(Comment $comment, Post $post) {
		$settings = $this->settings['notification']['comment'];
		$subject = $this->translator->translateById('comment.notification.subject', array($post->getTitle()), NULL, NULL, 'Main', 'Flowpack.Snippets');
		$message = $this->renderMessage($settings, array('post' => $post, 'comment' => $comment));
		$this->sendMail($settings, $subject, $message);
	}

	/**
	 * @param Post $post
	 * @return void
	 */
	public function sendNewPostNotification(Post $post) {
		$settings = $this->settings['notification']['post'];
		$subject = $this->translator->translateById('post.notification.subject', array($post->getTitle()), NULL, NULL, 'Main', 'Flowpack.Snippets');
		$message = $this->renderMessage($settings, array('post' => $post));
		$this->sendMail($settings, $subject, $message);
	}

	/**
	 * @param array $settings
	 * @param string $subject
	 * @param string $message
	 */
	protected function sendMail($settings, $subject, $message) {
		if ($settings['recipientAddress'] === '') return;

		$mail = new Message();
		$mail
			->setFrom(array($settings['senderAddress'] => $settings['senderName']))
			->setTo(array($settings['recipientAddress'] => $settings['recipientName']))
			->setSubject($subject)
			->setBody($message, $settings['format']);
		$mail->send();
	}

	/**
	 * @param array $settings
	 * @param array $params
	 * @return string
	 */
	protected function renderMessage($settings, $params) {
		$standaloneView = new StandaloneView();
		$standaloneView->setTemplatePathAndFilename($settings['templatePathAndFilename']);
		foreach ($params as $key => $value) {
			$standaloneView->assign($key, $value);
		}
		$message = $standaloneView->render();
		if ($settings['format'] === 'text/plain') {
			$message = strip_tags($message);
			$message = html_entity_decode($message);
		}
		return $message;
	}

}

?>