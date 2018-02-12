<?php

namespace app\services;

use app\helpers\Security;
use app\models\AccessToken;
use app\models\AccessTokenQuery;
use app\models\User;
use app\models\UserQuery;
use OAuth2\Storage\Pdo as PdoStorage;

/**
 * Class OAuth2Storage
 * @package Socialveo\Core\library
 */
class OAuth2Storage extends PdoStorage
{
    /**
     * Lifetime of token
     * @var int
     */
    private $lifetime = 86400; // 1 day

    /**
     * Set lifetime
     * @param int $lifetime
     */
    public function setLifetime($lifetime)
    {
        $this->lifetime = $lifetime;
    }

    /**
     * Returns lifetime
     * @return int
     */
    public function getLifetime()
    {
        return $this->lifetime;
    }

    /**
     * Retrieves access token from database
     * @param string $access_token
     * @return array|mixed
     */
    public function getAccessToken($access_token)
    {
        $token = AccessToken::findFirst((new AccessTokenQuery())->token($access_token)->getParams());
        if ($token) {
            $token = $token->toArray();
            // Convert date string back to timestamp (Avoid known bug in the OAuth2 library)
            $token['expires'] = strtotime($token['expires']);
        }

        return $token;
    }

    /**
     * Set access token
     * @param string $access_token
     * @param string $client_id [optional]
     * @param int $user_id [optional]
     * @param int $expires [optional]
     * @param string $scope [optional]
     * @return bool
     */
    public function setAccessToken($access_token, $client_id = null, $user_id = null, $expires = null, $scope = null)
    {
        // Convert expires to date string (Avoid known bug in the OAuth2 library)
        $expires = date('Y-m-d H:i:s', $expires ?: time() + $this->lifetime);

        /** @var AccessToken $token */
        $token = $this->getAccessToken($access_token);

        // if it not exists, then create it
        if (!$token) {
            $token = new AccessToken();
        }

        // Save access token
        $token->access_token = $access_token;
        $token->client_id = $client_id;
        $token->user_id = $user_id;
        $token->expires = $expires;

        return $token->save();
    }

    /**
     * Check user credentials
     * @param string $username User name or email
     * @param string $password User password
     * @return bool
     */
    public function checkUserCredentials($username, $password)
    {
        if ($user = $this->getUser($username)) {
            return $this->checkPassword($user, $password);
        }

        return false;
    }

    /**
     * Get user from database by user name or email
     * @param string $username User name or email
     * @return User|null|false
     */
    public function getUser($username)
    {
        // Fetch user by email
        if (strpos($username, '@') !== false) {
            $user = User::findFirst((new UserQuery())->email($username)->getParams());
        }
        // Fetch user by username
        else {
            $user = User::findFirst((new UserQuery())->name($username)->getParams());
        }
        return $user ? array_merge(['user_id' => $user->id], $user->toArray()) : false;
    }

    /**
     * Disable change user via OAuth2 for avoid security hole
     * @param string $username
     * @param string $password
     * @param mixed $firstName
     * @param mixed $lastName
     * @return bool
     */
    public function setUser($username, $password, $firstName = null, $lastName = null)
    {
        return false;
    }

    /**
     * Verifies password
     * @param array|User $user
     * @param string $password
     * @return bool
     */
    protected function checkPassword($user, $password)
    {
        return Security::verifyPassword($password, $user['pass']);
    }
}
