<?php

namespace BS\Traits;

use BS\ServiceLocatorAwareInterface;
use Interop\Container\ContainerInterface;
use BS\I18n\Translator\TranslatorAwareInterface;
use Psr\Log\LoggerAwareInterface;

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
                if ($container->has('logger')) {
                    $object->setLogger($container->get('logger'));
                }
            }

            if (method_exists($object, 'init')) {
                $object->init();
            }
        }
    }
}