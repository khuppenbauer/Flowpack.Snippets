<?php
namespace Flowpack\Snippets\Eel;


/*                                                                                                  *
 * This script belongs to the TYPO3 Flow package "Flowpack.Snippets".                               *
 *                                                                                                  *
 * It is free software; you can redistribute it and/or modify it under                              *
 * the terms of the GNU Lesser General Public License, either version 3                             *
 *  of the License, or (at your option) any later version.                                          *
 *                                                                                                  *
 * The TYPO3 project - inspiring people to share!                                                   *
 *                                                                                                  */

use TYPO3\Eel\ProtectedContextAwareInterface;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Reflection\ObjectAccess;
use TYPO3\Flow\Utility\Arrays;
use TYPO3\Flow\Core\Bootstrap;
use TYPO3\Flow\Mvc\Routing\Router;
use TYPO3\Flow\Property\PropertyMapper;

/**
 * Eel Helper to create TitleTag from Route Configuration
 */
class TitleTagHelper implements ProtectedContextAwareInterface {

	/**
	 * @var Router
	 * @Flow\Inject
	 */
	protected $router;

	/**
	 * @var Bootstrap
	 * @Flow\Inject
	 */
	protected $bootstrap;

	/**
	 * @var PropertyMapper
	 * @Flow\Inject
	 */
	protected $propertyMapper;


	/**
	 * Returns the titleTag if it could be resolved from a routing configuration
	 *
	 * @param string $routePath
	 * @param string $object
	 * @param string $property
	 * @return string
	 */
	public function resolveFromRoute($routePath, $object, $property) {
		$request = $this->bootstrap->getActiveRequestHandler();
		if ($request instanceof \TYPO3\Flow\Http\HttpRequestHandlerInterface) {
			$actionRequest = $this->router->route($request->getHttpRequest());
			$argument = Arrays::getValueByPath($actionRequest, $routePath);
			if (!empty($argument)) {
				$targetObject = $this->propertyMapper->convert($argument, $object);
				return ObjectAccess::getPropertyPath($targetObject, $property);
			}
		}
	}

	/**
	 * All methods are considered safe
	 *
	 * @param string $methodName
	 * @return boolean
	 */
	public function allowsCallOfMethod($methodName) {
		return TRUE;
	}
}