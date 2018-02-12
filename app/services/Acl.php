<?php

namespace app\services;

use Phalcon\Acl\Adapter\Memory;
use Phalcon\Acl\Resource;
use Phalcon\Acl\Role;
use Phalcon\Config;
use Phalcon\Di;
use Phalcon\Http\Response\Exception as HttpException;

/**
 * Class AclRole
 * @package app\services
 */
class Acl
{
    /**
     * Acl adapter
     * @var Memory
     */
    private $_acl;

    /**
     * Routes restrictions matrix
     * @var array
     */
    private $_routesRestrictions = [];

    /**
     * Initialize ACL service.
     * Register the types of acl from config
     */
    public function __construct()
    {
        // Create new acl instance
        $this->_acl = $acl = new Memory();
        // By default deny access to all
        $acl->setDefaultAction(\Phalcon\Acl::DENY);

        /** @var Config|\stdClass $config */
        $config = Di::getDefault()->get('config');

        // Save resources for avoid duplicates
        $resourcesList = [];

        // Parse acl configs, creates new roles and assign resources
        foreach ($config->acl->toArray() as $type => $data) {
            $role = $data['name'] ?? null;
            $description = $data['description'] ?? null;
            $resources = $data['resources'] ?? null;

            $aclRole = new Role($role, $description);
            $acl->addRole($aclRole);

            // Add resources
            if (!empty($resources) && is_array($resources)) {
                foreach ($resources as $resource => $access) {
                    // Avoid duplicates resources with the same name
                    $resource = $resourcesList[$resource] ?? $resourcesList[$resource] = new Resource($resource);

                    // Store all action for add to resource
                    $actionsList = [];
                    $restrictions = [];

                    foreach ($access as $restriction => $actions) {
                        if (!is_array($actions)) {
                            $actions = [$actions];
                        }
                        $actionsList = array_merge($actionsList, $actions);

                        $restrictions[$restriction] = $actions;
                    }

                    if ($actionsList) {
                        /** @var Resource $resource */
                        $acl->addResource($resource, array_unique($actionsList));

                        // Set allow/deny restrictions
                        foreach ($restrictions as $restriction => $actions) {
                            $acl->$restriction($role, $resource, $actions);
                        }
                    }
                }
            }
        }
    }

    /**
     * Add route restriction to acl
     * @param string $route
     * @param array|string $restrictions
     */
    public function addRoute(string $route, $restrictions)
    {
        $this->_routesRestrictions[$route] = $restrictions;
    }

    /**
     * Checks whether is allowed route for current user
     * @param string $route Route name (e.g. 'POST auth.login')
     * @throws HttpException
     */
    public function validateRouteRestrictions(string $route)
    {
        $action = 'access';
        $resourceName = '__allowedRoute';
        /** @var Resource $resource */
        $resource = new Resource($resourceName);
        $this->_acl->addResource($resource, $action);

        $restrictions = $this->_routesRestrictions[$route] ?? null;

        if ($restrictions && is_array($restrictions)) {
            foreach ($restrictions as $restriction => $roles) {
                foreach ($roles as $role) {
                    // Set allow/deny restrictions
                    $this->_acl->$restriction($role, $resource, $action);
                }
            }
        }

        // Check whether access is allowed or denied
        if (!$this->isAllowed($resource, $action)) {
            /** @var OAuth2Session $session */
            $session = Di::getDefault()->get('session');
            if ($session->getIsLogged()) {
                throw new HttpException('Access denied', 403);
            } else {
                throw new HttpException('Access denied', 401);
            }
        }
    }

    /**
     * Checks whether is allowed resource for current user
     * @param Resource|string $resource
     * @param array|string $actions
     * @return bool
     */
    public function isAllowed($resource, $actions)
    {
        /** @var OAuth2Session $session */
        $session = Di::getDefault()->get('session');
        $roles = $session->getUserRoles();

        $allowed = false;

        foreach ($roles as $role) {
            $allowed = $allowed || $this->_acl->isAllowed($role, $resource, $actions);
            if ($allowed) {
                return $allowed;
            }
        }

        return $allowed;
    }
}
