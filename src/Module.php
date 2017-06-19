<?php

namespace BS;

use BS\Db\Adapter\Profiler\AutoLogProfiler;
use BS\Db\Adapter\Profiler\Profiler;
use BS\Factory\ControllerAbstractFactory;
use BS\Factory\TableGatewayAbstractFactory;
use BS\Logger\Formatter\WildfireFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ControllerProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Session\Config\SessionConfig;
use Zend\Session\Container;
use Zend\Session\SessionManager;

/**
 * Class Module
 * @package BS
 */
abstract class Module implements ConfigProviderInterface, ServiceProviderInterface, ControllerProviderInterface
{
    const SESSION_LOCALE = 'LOCALE';

    public function onBootstrap(MvcEvent $event)
    {
        $eventManager = $event->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $this->initSession($event);
        $this->initLocale($event);
        $this->initLogger($event);
    }

    protected function initSession(MvcEvent $event)
    {
        $config = $event->getApplication()->getServiceManager()->get('Configuration');

        if (getenv('SESSION_SERVER')) {
            ini_set('session.save_handler', 'redis');
            if (getenv('SESSION_SERVER_DATABASE')) {
                $dataBase = getenv('SESSION_SERVER_DATABASE');
            } else {
                $dataBase = '0';
            }
            ini_set('session.save_path', 'tcp://' . gethostbyname(getenv('SESSION_SERVER')) . ':6379?database=' . $dataBase);
        }
        $sessionConfig = new SessionConfig();
        $sessionConfig->setOptions($config['session']);
        $sessionManager = new SessionManager($sessionConfig);

        $sessionManager->start();

        /**
         * Optional: If you later want to use namespaces, you can already store the
         * Manager in the shared (static) Container (=namespace) field
         */
        Container::setDefaultManager($sessionManager);
    }

    protected function initLocale(MvcEvent $event)
    {
        $Locale = new Container(self::SESSION_LOCALE);
        if ($Locale->{self::SESSION_LOCALE}) {
            $Transaltor = $event->getApplication()->getServiceManager()->get('translator');
            $Transaltor->setLocale($Locale->{self::SESSION_LOCALE});
            \Locale::setDefault($Locale->{self::SESSION_LOCALE});
        } else {
            $Locale->{self::SESSION_LOCALE} = 'en_GB';
        }

        return $Locale;
    }

    protected function initLogger(MvcEvent $event)
    {
        $ServiceManager = $event->getApplication()->getServiceManager();
        /**
         * @var Logger $logger
         * @var \Throwable $exception
         */
        $logger = $ServiceManager->get('logger');
        $handlers = $logger->getHandlers();
        foreach ($handlers as &$handler) {
            if ($handler instanceof StreamHandler) {
                //Make sure we reference the class directly so no error will be poped during production environment
                $Formatter = new \Bramus\Monolog\Formatter\ColoredLineFormatter(null, '%message% %context% %extra%');
                $Formatter->allowInlineLineBreaks(true);
                $Formatter->ignoreEmptyContextAndExtra(true);
                $handler->setFormatter($Formatter);
            }
        }
    }


    abstract public function getConfig();

    public function getControllerConfig()
    {
        return [
            'abstract_factories' => [
                ControllerAbstractFactory::class
            ],
        ];
    }

    public function getServiceConfig()
    {
        return [
            'abstract_factories' => [
                TableGatewayAbstractFactory::class
            ]
        ];
    }

    protected function initDbProfiler(MvcEvent $event)
    {
        $ServiceManager = $event->getApplication()->getServiceManager();
        /**
         * @var \Zend\Db\Adapter\Adapter $dbAdapter
         */
        $dbAdapter = $ServiceManager->get('db');
        if (defined('PHPUNIT_COMPOSER_INSTALL') || defined('__PHPUNIT_PHAR__')) {
            $profiler = new AutoLogProfiler();
            $profiler->setServiceLocator($ServiceManager);
            $dbAdapter->setProfiler($profiler);
        } else {
            $dbAdapter->setProfiler(new Profiler());
        }

        $EventManager = $event->getApplication()->getEventManager();
        $EventManager->attach(MvcEvent::EVENT_FINISH, [
            $this,
            'developmentEnvironmentDbProfilerLog',
        ]);
    }

    public function developmentEnvironmentDbProfilerLog(MvcEvent $event)
    {
        $ServiceManager = $event->getApplication()->getServiceManager();
        $dbAdapter = $ServiceManager->get('db');
        $profiles = $dbAdapter->getProfiler()->getProfiles();
        if (!in_array(php_sapi_name(), ['cli', 'phpdbg'])) {
            // Our special formatter to add 'TABLE' format for logging SQL Queries in FirePHP
            $FirePHPHandler = $ServiceManager->get('logger')->popHandler();
            $FirePHPHandler->setFormatter(new WildfireFormatter());
            $ServiceManager->get('logger')->pushHandler($FirePHPHandler);

            $quries = [['Eslape', 'SQL Statement', 'Parameters']];
            foreach ($profiles as $profile) {
                $quries[] =
                    [
                        round($profile['elapse'], 4),
                        $profile['sql'],
                        $profile['parameters'] ? $profile['parameters']->getNamedArray() : null,
                    ];
            }

            $ServiceManager->get('logger')->info('Queries', ['table' => $quries]);
        } else {
            $ServiceManager->get('logger')->info('Total Number of Queries : ' . count($profiles));
        }
    }
}
