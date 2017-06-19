<?php
/**
 * User: Allan Sun (allan.sun@bricre.com)
 * Date: 30/12/2015
 * Time: 18:24
 */

namespace BS\Authentication\Adapter;

use BS\Db\Model\AbstractModel;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\ServiceManager\ServiceLocatorInterface;


/**
 * Class DbTable
 *
 * @package Birdego\Authentication\Adapter
 * @codeCoverageIgnore
 * @method $this setCredential($credential)
 * @method $this setIdentity($identity)
 */
abstract class DbTable implements AdapterInterface
{
    protected $username = null;
    protected $password = null;
    protected $serviceLocator = null;
    /**
     * @var AbstractModel $resultRow
     */
    protected $resultRow = null;

    /**
     * DbTable constructor.
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function __construct($serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getResultRow()
    {
        return $this->resultRow->getArrayCopy();
    }
}