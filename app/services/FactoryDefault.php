<?php

namespace app\services;

use Phalcon\Di;
use Phalcon\Di\Service;

/**
 * Class FactoryDefault
 * @package app\services
 */
class FactoryDefault extends Di
{
    /**
     * Phalcon\Di\FactoryDefault constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->_services = [
            'acl'=>                new Service('acl', 'app\\services\\Acl', true),
//            'session'=>            new Service('session', 'app\\services\\OAuth2Session', true),
//            'router'=>             new Service('router', 'app\\services\\Router', true),
            'eventListener'=>      new Service('eventListener', 'app\\plugins\\EventListener', true),
            'dispatcher'=>         new Service('dispatcher', 'Phalcon\\Mvc\\Dispatcher', true),
            'url'=>                new Service('url', 'Phalcon\\Mvc\\Url', true),
            'modelsManager'=>      new Service('modelsManager', 'Phalcon\\Mvc\\Model\\Manager', true),
            'modelsMetadata'=>     new Service('modelsMetadata', 'Phalcon\\Mvc\\Model\\MetaData\\Memory', true),
            'response'=>           new Service('response', 'Phalcon\\Http\\Response', true),
            'request'=>            new Service('request', 'Phalcon\\Http\\Request', true),
            'filter'=>             new Service('filter', 'Phalcon\\Filter', true),
            'escaper'=>            new Service('escaper', 'Phalcon\\Escaper', true),
            'security'=>           new Service('security', 'Phalcon\\Security', true),
            'crypt'=>              new Service('crypt', 'Phalcon\\Crypt', true),
            'eventsManager'=>      new Service('eventsManager', 'Phalcon\\Events\\Manager', true),
            'transactionManager'=> new Service('transactionManager', 'Phalcon\\Mvc\\Model\\Transaction\\Manager', true),
        ];
    }
}
