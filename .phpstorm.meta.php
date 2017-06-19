<?php
namespace PHPSTORM_META {

    use Psr\Container\ContainerInterface;
    use Zend\I18n\Translator\Translator;
    use Zend\Cache\Storage\StorageInterface;

    override(
        ContainerInterface::get(0),
        map([
            'translator' => Translator::class,
            'cache'      => StorageInterface::class
        ])
    );
}