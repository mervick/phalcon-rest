<?php

namespace app\forms\auth;

use app\forms\base\Form;
use app\forms\validation\ReCaptcha;
use app\models\User;
use app\models\UserQuery;
use Phalcon\Di;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Validation\Validator\PresenceOf;

/**
 * Class LoginForm
 * @package app\forms\auth
 */
class LoginForm extends Form
{
    /**
     * Initialize form
     */
    public function initialize()
    {
        // Add username/email input
        $username = new Hidden('username');
        $username->addValidator(new PresenceOf());
        $this->add($username);

        // Add password input
        $password = new Hidden('password');
        $password->addValidator(new PresenceOf());
        $this->add($password);

        // Add reCaptcha response input
        $reCaptcha = new Hidden('g-recaptcha-response');
        $reCaptcha->addValidator(new PresenceOf([
            'message' => 'ReCaptcha validation is required',
        ]));
        $reCaptcha->addValidator(new ReCaptcha());
        $this->add($reCaptcha);
    }

    /**
     * Checks input data and log in
     * @return User|mixed|null
     * @throws \Exception
     */
    public function login()
    {
        $username = $this->get('username')->getValue();
        $password = $this->get('password')->getValue();

        // Fetch user by email
        if (strpos($username, '@') !== false) {
            $user = User::findFirst((new UserQuery())->email($username)->getParams());
        }
        // Fetch user by username
        else {
            $user = User::findFirst((new UserQuery())->name($username)->getParams());
        }

        // Verify password
        return $user && password_verify($password, $user->pass);
    }
}
