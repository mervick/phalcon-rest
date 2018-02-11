<?php

namespace app\services;

use app\models\User;
use OAuth2\Response as OAuth2Response;
use OAuth2\Request as OAuth2Request;
use OAuth2\Server as OAuth2Server;
use Phalcon\Di;
use Phalcon\Http\Response;
use Phalcon\Http\ResponseInterface;

/**
 * Class OAuth2Session
 * @package app\services
 */
class OAuth2Session
{
    /**
     * Logged user id
     * @var int
     */
    public $id;

    /**
     * User model
     * @var User|null
     */
    private $_user;

    /**
     * Access token
     * @var array
     */
    private $_accessToken;

    /**
     * OAuth2Session constructor.
     * Login via access token
     * @param OAuth2Server $OAuth2Server
     */
    public function __construct($OAuth2Server)
    {
        // Read data from the request
        $request = new OAuth2Request($_GET, $_POST, [], [], [], $_SERVER);

        // OAuth2 authentication
        if ($token = $OAuth2Server->getAccessTokenData($request)) {
            if ($user = User::findFirst($token['user_id'])) {
                $this->_user = $user;
                $this->id = $user->id;
                $this->_accessToken = $token;
            }
        }

        /** @var OAuth2Response $OAuth2Response */
        $OAuth2Response = $OAuth2Server->getResponse();
        $statusCode = $OAuth2Response->getStatusCode();

        // If something wrong when set status code and response directly from OAuth2 server
        if ($statusCode != 200 && $statusCode != 401) {
            /** @var Response|ResponseInterface $response */
            $response = Di::getDefault()->get('response');
            $response->setStatusCode($statusCode);

            foreach ($OAuth2Response->getHttpHeaders() as $name => $value) {
                $response->setHeader($name, $value);
            }
        }
    }

    /**
     * Returns whether user is logged
     * @return bool
     */
    public function getIsLogged()
    {
        return !is_null($this->_user);
    }

    /**
     * Returns logged user model
     * @return null|User
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * Returns logged user access token
     * @return null|array
     */
    public function getAccessToken()
    {
        return $this->_accessToken;
    }

    /**
     * Returns user roles
     * @return array
     */
    public function getUserRoles()
    {
        if ($this->getIsLogged()) {
            $roles[] = ACL_USER;
        } else {
            $roles[] = ACL_GUEST;
        }

        return $roles;
    }
}
