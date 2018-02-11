<?php

namespace app\models;

use Phalcon\Mvc\Model;

/**
 * Class AccessToken
 * @package app\models
 */
class AccessToken extends Model
{
    /**
     * Access token
     * @var string
     */
    public $access_token;

    /**
     * User id
     * @var integer
     */
    public $user_id;

    /**
     * Client id
     * @var string
     */
    public $client_id;

    /**
     * Expiration timestamp
     * @var string
     */
    public $expires;

    /**
     * Scope
     * @var string
     */
    public $scope;

    /**
     * Define table source
     * @return string
     */
    public function getSource()
    {
        return 'oauth2_access_token';
    }
}