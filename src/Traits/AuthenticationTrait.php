<?php

namespace BS\Traits;

use BS\Authentication\AuthenticationService;
use BS\Authentication\AuthenticationServiceFactory;
use BS\Authentication\AuthenticationServiceNotAvailableException;
use BS\Authentication\UnAuthenticatedException;
use BS\Controller\AbstractRestfulController;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class AuthenticationTrait
 *
 * @package Birdego\Traits
 * @method String getModuleName()
 * @method String getControllerName()
 * @method String getActionName()
 * @codeCoverageIgnore
 */
trait AuthenticationTrait
{
    /**
     * @var AuthenticationService
     */
    protected $AuthenticationService;
    protected $requireLogin = true;

    private static $moduleName;

    public function getUserInfo()
    {
        return $this->getAuthenticationService()->getUserInfo();
    }

    /**
     * @param bool $value
     */
    public function setRequireLogin($value = true)
    {
        $this->requireLogin = $value;
    }

    /**
     * @return AuthenticationService
     * @throws AuthenticationServiceNotAvailableException
     */
    public function getAuthenticationService()
    {
        /** @var AbstractRestfulController $this */
        if (!$this->serviceLocator->has('AuthService')) {
            throw new AuthenticationServiceNotAvailableException;
        }

        return $this->serviceLocator->get('AuthService');
    }

    public function initAuthenticationService(MvcEvent $event)
    {
        /**
         * @var AbstractRestfulController $this
         * @var AuthenticationServiceFactory $AuthenticationServiceFactory
         * @var ServiceLocatorInterface $serviceLocator
         */

        $controller = $event->getTarget();
        $controllerClass = get_class($controller);
        $moduleNamespace = substr($controllerClass, 0, strpos($controllerClass, '\\'));

        if (!$this->serviceLocator->has('AuthService') || self::$moduleName != $moduleNamespace) {
            self::$moduleName = $moduleNamespace;
            $headers = $this->getRequest()->getHeaders();

            $AuthenticationServiceFactory = $this->serviceLocator->get('AuthServiceFactory');
            $this->serviceLocator->setService('AuthService', $AuthenticationServiceFactory->create($moduleNamespace));

            // ZF2 doesn't pass all headers from server, so we need to manually add them if existed
            $headersFromServer = self::getallheaders();
            $headersFromServer = array_merge($headersFromServer, $_GET);

            $this->checkHeader($headersFromServer, $headers);
        }

        if ($this->requireLogin) {
            if (!$this->getAuthenticationService()->hasIdentity()) {
                throw new UnAuthenticatedException();
            }
            $this->checkUserPrivilege();
        }
    }

    public static function getallheaders()
    {
        if (!function_exists('getallheaders')) {
            if (!is_array($_SERVER)) {
                return array();
            }

            $headers = array();
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }

            return $headers;
        } else {
            return getallheaders();
        }
    }

    protected function checkHeader($serverHeaders, $headers)
    {

    }

    protected function checkUserPrivilege()
    {

    }

    protected function fromCamelCase($input)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }

        return implode('-', $ret);
    }

}
