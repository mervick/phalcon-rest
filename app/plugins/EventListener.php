<?php

namespace app\plugins;

use app\services\Acl;
use app\services\Router;
use Phalcon\Di;
use Phalcon\Events\Event;
use Phalcon\Http\Request;
use Phalcon\Mvc\Router\Route;
use Phalcon\Mvc\User\Plugin;
use Phalcon\Http\Response\Exception as HttpException;

/**
 * Class EventListener
 * @package app\plugins
 */
class EventListener  extends Plugin
{
    /**
     * Matched route event,
     * it triggers before action will be executed
     * @param Event $event
     * @param Router $router
     * @throws HttpException
     */
    public function afterCheckRoutes($event, $router)
    {
        /** @var Acl $acl */
        $acl = Di::getDefault()->get('acl');
        /** @var Route $route */
        $route = $router->getMatchedRoute();

        // No matched route found
        if (!$route || !($name = $route->getName())) {
            throw new HttpException('Page Not Found', 404);
        }

        // Get allowed methods
        $allowedMethods = explode(',', strtoupper($route->getPaths()['@methods']));
        /** @var Request $request */
        $request = Di::getDefault()->get('request');
        $requestedMethod = $request->getMethod();

        // Check whether method is allowed or not
        if (!in_array($requestedMethod, $allowedMethods)) {
            throw new HttpException('Method is not allowed', 405);
        }

        // Checks ACL restrictions
        $acl->validateRouteRestrictions($name);
    }
}
