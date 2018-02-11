<?php

namespace app\models;

use Phalcon\Mvc\Model;

/**
 * Class User
 * Implements common user model
 * @package app\models
 */
class User extends Model
{
    /**
     * User id
     * @var
     */
    public $id;

    /**
     * User email
     * @var string
     */
    public $email;

    /**
     * User name
     * @var string
     */
    public $name;

    /**
     * User firstname
     * @var string
     */
    public $firstname;

    /**
     * User lastname
     * @var string
     */
    public $lastname;

    /**
     * User hashed password
     * @var
     */
    public $pass;

    /**
     * Define user table source
     * @return string
     */
    public function getSource()
    {
        return 'user';
    }
}
