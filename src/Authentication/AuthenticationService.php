<?php
/**
 * User: Allan Sun (allan.sun@bricre.com)
 * Date: 31/12/2015
 * Time: 15:30
 */

namespace BS\Authentication;

use BS\Authentication\Storage\Session;
use BS\Db\Model\UserInfo;
use Zend\Authentication\AuthenticationService as Base;

/**
 * Class AuthenticationService
 *
 * @package Birdego\Authentication
 * @method Session getStorage()
 * @codeCoverageIgnore
 */
abstract class AuthenticationService extends Base
{
    private static $UserInfo = [];

    /**
     * @return mixed
     * @throws UnAuthenticatedException
     */
    public function getUserInfo()
    {
        if ($this->hasIdentity()) {
            $storage = $this->getStorage();
            $StorageNameSpace = $storage->getNamespace();

            if (!isset(static::$UserInfo[$StorageNameSpace])) {
                $UserInfo = $this->getUserModelByModule($StorageNameSpace);
                if ($UserInfo instanceof UserInfo) {
                    $UserInfo->exchangeArray($storage->read());
                    static::$UserInfo[$StorageNameSpace] = $UserInfo;
                } else {
                    throw new UnAuthenticatedException;
                }
            }

            return static::$UserInfo[$StorageNameSpace];
        }
        throw new UnAuthenticatedException;
    }

    abstract function getUserModelByModule($module);
}