<?php
/**
 * User: Allan Sun (allan.sun@bricre.com)
 * Date: 29/12/2015
 * Time: 00:16
 */

namespace BS\Authentication;

use BS\Authentication\Adapter\DbTable;
use BS\Authentication\Storage\Session;
use BS\Exception;
use BS\ServiceLocatorAwareInterface;
use BS\Traits\ServiceLocatorAwareTrait;

abstract class AuthenticationServiceFactory implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    /**
     * @var AuthenticationService[]
     */
    protected static $instances = [];

    /**
     * @var Session[]
     */
    protected static $storages = [];

    /**
     * @param $module
     *
     * @return AuthenticationService
     * @throws Exception
     */
    public function create($module)
    {
        $module = strtoupper($module);
        if (array_key_exists($module, static::$instances)) {
            return static::$instances[$module];
        }
        static::$instances[$module] = $this->getAuthenticationService($this->getStorage($module), $this->getAuthAdapter($module));

        return static::$instances[$module];
    }

    /**
     * @param Session $session
     * @param DbTable $adapter
     * @return AuthenticationService
     */
    abstract function getAuthenticationService(Session $session, DbTable $adapter);

    /**
     * @param $module
     * @return DbTable
     */
    abstract function getAuthAdapter($module);

    /**
     * @param $module
     *
     * @return Session
     */
    public function getStorage($module)
    {
        if (!array_key_exists($module, static::$storages)) {
            $session = new Session($module, 'UserInfo');
            //setExpirationSeconds here not only just set TLL
            //but also clean expiry session data from storage
            $session->setExpirationSeconds(ini_get('session.gc_maxlifetime'));
            static::$storages[$module] = $session;
        }

        return static::$storages[$module];
    }


}