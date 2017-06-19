<?php
/**
 * User: Allan Sun (allan.sun@bricre.com)
 * Date: 28/12/2015
 * Time: 16:49
 */

namespace BS\Tests;

use BS\ServiceLocatorAwareInterface;
use BS\Traits\LoggerAwareTrait;
use BS\Traits\ServiceLocatorAwareTrait;
use Faker\Factory as FackerFactory;
use Faker\Generator;
use Psr\Log\LoggerAwareInterface;
use Zend\Http\Request;
use Zend\Json\Json;
use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;

/**
 * Class AbstractTestCase
 *
 * @package Birdego\Tests
 * @method Request getRequest()
 */
abstract class AbstractTestCase extends AbstractControllerTestCase implements LoggerAwareInterface, ServiceLocatorAwareInterface
{
    use LoggerAwareTrait, ServiceLocatorAwareTrait;

    /**
     * @see https://github.com/fzaninotto/Faker
     * @var Generator $faker
     */
    private static $faker;

    protected $traceError = false;

    protected $appConfigPath;

    function setUp()
    {
        parent::setUp();
        $this->setApplicationConfig(include $this->appConfigPath);
        if (!$this->getLogger()) {
            $this->setLogger($this->getApplicationServiceLocator()->get('logger'));
        }

        if ($this->getName()) {
            $this->getLogger()->notice('============ [' . get_class($this) . '::' . $this->getName() . '] ===========');
        }
    }

    function tearDown()
    {
        parent::tearDown();
        $this->reset();
    }

    /**
     * @return Generator
     */
    public function getFaker()
    {
        if (!self::$faker) {
            self::$faker = FackerFactory::create('en_GB');
        }

        return self::$faker;
    }

    /**
     * @return \stdClass
     */
    protected function getJsonResponseContent()
    {
        return Json::decode($this->getResponse()->getContent());

    }

    /**
     * @return \Interop\Container\ContainerInterface|\Zend\ServiceManager\ServiceManager
     */
    public function getApplicationServiceLocator()
    {
        if ($this->serviceLocator) {
            return $this->serviceLocator;
        } else {
            return parent::getApplicationServiceLocator();
        }
    }
}