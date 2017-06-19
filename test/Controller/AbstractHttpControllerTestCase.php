<?php

namespace BS\Tests\Controller;

use BS\Tests\Db\TableGateway\AbstractTableGatewayTest;
use BS\Exception\AbstractWithParamException;
use BS\Traits\LoggerAwareTrait;
use PHPUnit_Framework_ExpectationFailedException;
use Psr\Log\LoggerInterface;
use Zend\I18n\Translator\Translator;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase as Base;

/**
 * Class AbstractBirdegoHttpControllerTestCase
 *
 * @package Birdego\Tests\Controller
 */
abstract class AbstractHttpControllerTestCase extends Base
{
    use LoggerAwareTrait;

    protected $backupGlobals = false;
    protected $traceError = false;

    /**
     * @var string TestCase class for TableGateway used in this controller
     */
    protected $tableGatewayTestClass;

    /**
     * @var Translator
     */
    protected static $translator;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    protected function setUp()
    {
        $this->setApplicationConfig($this->getAppConfig());
        $this->setLogger($this->getApplicationServiceLocator()->get('logger'));
        if ($this->getName()) {
            $this->getLogger()->notice('============ [' . get_class($this) . '::' . $this->getName() . '] ===========');
        }
        $this->authenticate();
    }

    abstract function getAppConfig();

    /**
     * @param null $class
     *
     * @return AbstractTableGatewayTest
     */
    public function getTableGatewayTest($class = null)
    {
        /**
         * @var AbstractTableGatewayTest $tableGatewayTest
         */
        if ($class) {
            $tableGatewayTest = $this->getApplicationServiceLocator()->get($class);
        } else {
            $tableGatewayTest = new $this->tableGatewayTestClass;
            $tableGatewayTest->setUp();
        }

        return $tableGatewayTest;
    }

    /**
     * This method is supposed to be extended by traits in correct modules.
     *
     * @param bool $forceAuthenticate
     *
     * @return $this
     */
    abstract function authenticate($forceAuthenticate = false);

    public function getLogger()
    {
        if (!$this->logger) {
            $this->logger = $this->getApplication()->getServiceManager()->get('logger');
        }

        return $this->logger;
    }


    protected function checkJsonResponse()
    {
        $json = json_decode($this->getResponse()->getContent(), true);
        $this->assertNotFalse($json);
        $this->assertArrayHasKey('total', $json);
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('success', $json);

        $this->assertTrue(is_numeric($json['total']));
        $this->assertTrue($json['success']);
        $this->assertTrue(is_array($json['data']));

        return $json;
    }

    protected function t($message)
    {
        if (!static::$translator) {
            static::$translator = $this->getApplicationServiceLocator()->get('translator');
        }

        return static::$translator->translate($message);
    }

    public function assertApplicationException($type, $message = null)
    {
        parent::assertApplicationException($type, $message ?: '');
    }

    public function assertParamException($type, $messageParams = [], $messageReg = '', $code = null)
    {
        $exception = $this->getApplication()->getMvcEvent()->getParam('exception');
        if (!$exception) {
            throw new PHPUnit_Framework_ExpectationFailedException(
                'Failed asserting application exception, exception not exist'
            );
        }
        if (true === $this->traceError) {
            // set exception as null because we know and have assert the exception
            $this->getApplication()->getMvcEvent()->setParam('exception', null);
        }

        if (!($exception instanceof AbstractWithParamException)) {
            throw new PHPUnit_Framework_ExpectationFailedException(
                sprintf('The exception [%s] is not extend from AbstractWithParamException', $exception)
            );
        }

        if ($messageReg) {
            $this->assertRegExp($messageReg, $exception->getMessage());
        }

        if (!empty($messageParams)) {
            $this->assertEquals($exception->getMessageParams(), $messageParams);
            $this->log(vsprintf($this->t($exception->getMessage()), $exception->getMessageParams()));
        }

    }

    /**
     * Assert response status code
     *
     * @param int $code
     */
    public function assertResponseStatusCode($code)
    {
        if ($this->useConsoleRequest) {
            if (!in_array($code, [0, 1])) {
                throw new PHPUnit_Framework_ExpectationFailedException(
                    'Console status code assert value must be O (valid) or 1 (error)'
                );
            }
        }
        $match = $this->getResponseStatusCode();
        if ($code != $match) {
            throw new PHPUnit_Framework_ExpectationFailedException(
                sprintf('Failed asserting response code "%s", actual status code is "%s", response: %s', $code, $match,
                    $this->getResponse()->getContent())
            );
        }
        $this->assertEquals($code, $match);
    }
}
