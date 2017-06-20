<?php

namespace BS\Traits;

use BS\ServiceLocatorAwareInterface;
use Interop\Container\ContainerInterface;
use BS\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\Log\LoggerAwareInterface;

trait InjectServiceFromAwareInterfaceTrait
{
    protected function checkAwareInterface($object, ContainerInterface $container)
    {
        if (is_object($object)) {
            if ($object instanceof ServiceLocatorAwareInterface) {
                $object->setServiceLocator($container);
            }

            if ($object instanceof TranslatorAwareInterface) {
                if ($container->has('translator')) {
                    $object->setTranslator($container->get('translator'));
                }
            }

            if ($object instanceof LoggerAwareInterface) {
                if ($container->has(LoggerAwareInterface::class)) {
                    $object->setLogger($container->get(LoggerAwareInterface::class));
                }
            }

            if (method_exists($object, 'init')) {
                $object->init();
            }
        }
    }
}