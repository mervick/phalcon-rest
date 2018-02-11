<?php

namespace app\services;

use Phalcon\Di;

/**
 * Class Router
 * @package app\services
 */
class Router extends \Phalcon\Mvc\Router
{
    /**
     * Mount routes from array
     * @throws \Exception
     */
    public function mountRoutes()
    {
        /** @var Config|\stdClass $config */
        $config = Di::getDefault()->get('config');
        $defaultNS = $config->application->defaultNamespace;
        $routes = $config->routes->toArray();

        /** @var Acl $acl */
        $acl = Di::getDefault()->get('acl');
        $aclKeys = array_flip(['allow', 'deny']);

        $this->setDefaultController($config->application->defaultController);
        $this->setDefaultNamespace($defaultNS);

        foreach ($routes as $controller => $data) {
            if (!empty($data)) {

                foreach ($data as $pattern => $methods) {
                    foreach ($methods as $method => $paths) {

                        // Get acl config from route paths
                        $restrictions = array_intersect_key($paths, $aclKeys);

                        // Clear acl config from route paths
                        $paths = array_diff_key($paths, $restrictions);
                        $paths['controller'] = $controller;
                        $paths['namespace'] = $defaultNS;

                        // Hackable trick for handle requests to not allowed methods and send valid HTTP code
                        $paths['@methods'] = $method;

                        $action = $paths['action'] ?? null;
                        if (!$action) {
                            throw new \Exception('Undefined endpoint action');
                        }

                        $routeName = "{$method} {$controller}.{$action}";
                        // Create route endpoint
                        $this->add("/$controller/$pattern", $paths/*, $method*/)->setName($routeName);

                        // Add route restrictions to ACL service
                        $acl->addRoute($routeName, $restrictions);
                    }
                }
            }
        }
    }
}
