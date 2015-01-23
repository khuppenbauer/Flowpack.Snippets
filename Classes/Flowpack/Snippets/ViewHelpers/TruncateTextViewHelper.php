<?php
namespace Flowpack\Snippets\ViewHelpers;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".     *
 *                                                                        *
 *                                                                        */

use Symfony\Component\DomCrawler\Crawler;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\Flow\Mvc\ActionRequest;

/**
 * ViewHelper that truncates the given text - adopted from cakephp
 *
 * == Examples ==
 *
 */
class TruncateTextViewHelper extends AbstractViewHelper {

	/**
	 * Truncates text.
	 *
	 * Cuts a string to the length of $length and replaces the last characters
	 * with the ellipsis if the text is longer than length.
	 *
	 * ### Options:
	 *
	 * - `ellipsis` Will be used as Ending and appended to the trimmed string (`ending` is deprecated)
	 * - `exact` If false, $text will not be cut mid-word
	 * - `html` If true, HTML tags would be handled correctly
	 *
	 * @param string $text String to truncate.
	 * @param int $length Length of returned string, including ellipsis.
	 * @param array $options An array of html attributes and options.
	 * @return string Trimmed string.
	 * @link http://book.cakephp.org/2.0/en/core-libraries/helpers/text.html#TextHelper::truncate
	 */
	public function render($text, $length = 100, $options = array()) {
		$defaults = array(
				'ellipsis' => ' ...', 'exact' => false, 'html' => true
		);
		if (isset($options['ending'])) {
			$defaults['ellipsis'] = $options['ending'];
		} elseif (!empty($options['html']) && Configure::read('App.encoding') === 'UTF-8') {
			$defaults['ellipsis'] = "\xe2\x80\xa6";
		}
		$options += $defaults;
		extract($options);
		if (!function_exists('mb_strlen')) {
			class_exists('Multibyte');
		}
		if ($html) {
			if (mb_strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
				return $text;
			}
			$totalLength = mb_strlen(strip_tags($ellipsis));
			$openTags = array();
			$truncate = '';
			preg_match_all('/(<\/?([\w+]+)[^>]*>)?([^<>]*)/', $text, $tags, PREG_SET_ORDER);
			foreach ($tags as $tag) {
				if (!preg_match('/img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param/s', $tag[2])) {
					if (preg_match('/<[\w]+[^>]*>/s', $tag[0])) {
						array_unshift($openTags, $tag[2]);
					} elseif (preg_match('/<\/([\w]+)[^>]*>/s', $tag[0], $closeTag)) {
						$pos = array_search($closeTag[1], $openTags);
						if ($pos !== false) {
							array_splice($openTags, $pos, 1);
						}
					}
				}
				$truncate .= $tag[1];
				$contentLength = mb_strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $tag[3]));
				if ($contentLength + $totalLength > $length) {
					$left = $length - $totalLength;
					$entitiesLength = 0;
					if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $tag[3], $entities, PREG_OFFSET_CAPTURE)) {
						foreach ($entities[0] as $entity) {
							if ($entity[1] + 1 - $entitiesLength <= $left) {
								$left--;
								$entitiesLength += mb_strlen($entity[0]);
							} else {
								break;
							}
						}
					}
					$truncate .= mb_substr($tag[3], 0, $left + $entitiesLength);
					break;
				} else {
					$truncate .= $tag[3];
					$totalLength += $contentLength;
				}
				if ($totalLength >= $length) {
					break;
				}
			}
		} else {
			if (mb_strlen($text) <= $length) {
				return $text;
			}
			$truncate = mb_substr($text, 0, $length - mb_strlen($ellipsis));
		}
		if (!$exact) {
			$spacepos = mb_strrpos($truncate, ' ');
			if ($html) {
				$truncateCheck = mb_substr($truncate, 0, $spacepos);
				$lastOpenTag = mb_strrpos($truncateCheck, '<');
				$lastCloseTag = mb_strrpos($truncateCheck, '>');
				if ($lastOpenTag > $lastCloseTag) {
					preg_match_all('/<[\w]+[^>]*>/s', $truncate, $lastTagMatches);
					$lastTag = array_pop($lastTagMatches[0]);
					$spacepos = mb_strrpos($truncate, $lastTag) + mb_strlen($lastTag);
				}
				$bits = mb_substr($truncate, $spacepos);
				preg_match_all('/<\/([a-z]+)>/', $bits, $droppedTags, PREG_SET_ORDER);
				if (!empty($droppedTags)) {
					if (!empty($openTags)) {
						foreach ($droppedTags as $closingTag) {
							if (!in_array($closingTag[1], $openTags)) {
								array_unshift($openTags, $closingTag[1]);
							}
						}
					} else {
						foreach ($droppedTags as $closingTag) {
							$openTags[] = $closingTag[1];
						}
					}
				}
			}
			$truncate = mb_substr($truncate, 0, $spacepos);
		}

		if ($html) {
			foreach ($openTags as $tag) {
				$truncate .= '</' . $tag . '>';
			}
		}

		$crawler = new Crawler($truncate);
		$lastNode = $crawler->filter('body')->children()->last()->getNode(0);
		if ($lastNode->nodeName === 'pre') {
			$parent = $lastNode->parentNode;
			$parent->removeChild($lastNode);
		}
		return utf8_decode($crawler->filter('body')->text()) . ' ' . $ellipsis;
	}

}
