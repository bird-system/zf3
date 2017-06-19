<?php

namespace BS\Factory;

use BS\Tests\Db\TableGateway\AbstractTableGatewayTest;
use BS\Traits\InjectServiceFromAwareInterfaceTrait;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;
use Interop\Container\ContainerInterface;

class TableGatewayTestsAbstractFactory implements AbstractFactoryInterface
{
    use InjectServiceFromAwareInterfaceTrait;

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $instance = new $requestedName();

        $this->checkAwareInterface($instance, $container);

        return $instance;
    }

    public function canCreate(ContainerInterface $container, $requestedName)
    {
        if (class_exists($requestedName)) {
            if (array_key_exists(AbstractTableGatewayTest::class, class_parents($requestedName))) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}