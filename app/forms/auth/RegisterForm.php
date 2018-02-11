<?php

namespace app\forms\auth;

use app\forms\base\Form;
use app\forms\validation\ReCaptcha;
use app\helpers\Security;
use app\models\User;
use Phalcon\Config;
use Phalcon\Di;
use Phalcon\Forms\Element;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;
use Phalcon\Mvc\Model\Manager;
use Phalcon\Mvc\Model\MetaDataInterface;
use Phalcon\Mvc\Model\Relation;
use Phalcon\Validation\Validator\Confirmation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Regex;
use Phalcon\Validation\Validator\Uniqueness;
use Phalcon\Validation\Validator\Email;

/**
 * Class RegisterForm
 * @package app\forms\auth
 */
class RegisterForm extends Form
{
    /**
     * Initialize form
     */
    public function initialize()
    {
        /** @var Config|\stdClass $config */
        $config = Di::getDefault()->get('config');

        $user = new User();

        // Add username input
        $username = new Hidden('name');
        $username->addValidator(new PresenceOf());
        $username->addValidator(new Uniqueness([
            'model' => $user,
            'message' => 'Username not unique and already used by someone',
        ]));
        $this->add($username);

        // Add email input
        $email = new Hidden('email');
        $email->addValidator(new PresenceOf());
        $email->addValidator(new Email([
            'message' => 'The email is not valid',
        ]));
        $email->addValidator(new Uniqueness([
            'model' => $user,
            'message' => 'Email already used',
        ]));
        $this->add($email);

        // Add password input
        $password = new Hidden('password');
        $password->addValidator(new PresenceOf());
        $password->addValidator(new Regex([
            'pattern' => $config->security->passwordRegex,
            'message' => 'Password is too weak try more secure',
        ]));
        $password->addValidator(new Confirmation([
            'message' => 'Password doesn\'t match confirmation',
            'with' => 'password_confirm',
        ]));
        $this->add($password);

        // Add password confirm input
        $passwordConfirm = new Hidden('password_confirm');
        $passwordConfirm->addValidator(new PresenceOf([
            'message' => 'Field password confirmation is required',
        ]));
        $this->add($passwordConfirm);

        // Add firstname input
        $firstname = new Hidden('firstname');
        $this->add($firstname);

        // Add lastname input
        $lastname = new Hidden('lastname');
        $this->add($lastname);

        // Add reCaptcha response input
        $reCaptcha = new Hidden('g-recaptcha-response');
        $reCaptcha->addValidator(new PresenceOf([
            'message' => 'ReCaptcha validation is required',
        ]));
        $reCaptcha->addValidator(new ReCaptcha());
        $this->add($reCaptcha);
    }

    /**
     * Save changes of form in case if all is fine.
     * Register new user
     * @return User|mixed|null
     * @throws \Exception
     */
    public function register()
    {
        /** @var Element[] $elements */
        $elements = $this->getElements();

        // Create new user
        $user = new User();

        // Get user model properties
        // Get model fields from model metadata
        /** @var MetaDataInterface $modelsMetadata */
        $modelsMetadata = Di::getDefault()->get('modelsMetadata');
        $fields = $modelsMetadata->getDataTypes($user);

        // Fill model fields with the form values
        foreach ($elements as $element) {
            $name = $element->getName();
            if (isset($fields[$name])) {
                $user->$name = $element->getValue();
            }
        }

        // Generate hashed password for user
        $user->pass = Security::generateHashPassword($this->get('password')->getValue());

        if (!$user->save()) {
            throw new \Exception($user->getMessages()[0]->getMessage());
        }

        return $user;
    }
}
