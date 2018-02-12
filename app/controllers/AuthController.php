<?php

namespace app\controllers;

use app\controllers\base\RestController;
use app\forms\auth\LoginForm;
use app\forms\auth\RegisterForm;
use app\models\AccessToken;
use app\models\AccessTokenQuery;
use app\services\OAuth2Session;
use OAuth2\Server as OAuth2Server;
use OAuth2\Request as OAuth2Request;
use OAuth2\Response as OAuth2Response;
use Phalcon\Di;
use Phalcon\Forms\Element;
use Phalcon\Mvc\Url;
use Phalcon\Http\Response\Exception as HttpException;

/**
 * Class AuthController
 * @package app\controllers
 * @property OAuth2Server $oauth2server
 * @property OAuth2Session $session
 */
class AuthController extends RestController
{
    /**
     * Login endpoint
     * Type POST
     * @return array
     * @throws HttpException
     */
    public function loginAction()
    {
        // Load data using login form an validate it
        $form = new LoginForm();

        if ($form->isValid($this->request->getPost())) {
            if ($user = $form->login()) {

                $POST = array_merge($_POST, [
                    'grant_type' => 'password',
                    'client_id' => 'test',
                ]);

                $request = new OAuth2Request([], $POST, [], [], [], $_SERVER);
                $params = $this->oauth2server->handleTokenRequest($request)->getParameters();

                if (!$params || !isset($params['access_token'])) {
                    throw new HttpException('Internal Server Error', 500);
                }

                return [
                    'status' => 'OK',
                    'message' => 'You was successfully logged in',
                    'access_token' => array_intersect_key($params, array_flip(['token_type', 'access_token'])),
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Incorrect username or password.',
                ];
            }
        }

        $this->response->setStatusCode(400);
        return array_merge([
            'status' => 'error',
            'message' => 'The form has validation errors',
            'errors' => $form->getErrorsCollection(),
        ]);
    }

    /**
     * Register endpoint
     * Type POST
     * @return array
     */
    public function registerAction()
    {
        // Load data using registration form an validate it
        $form = new RegisterForm();

        if ($form->isValid($this->request->getPost())) {
            $form->register();

            return [
                'status' => 'OK',
                'message' => 'You was successfully registered',
            ];
        }

        $this->response->setStatusCode(400);
        return array_merge([
            'status' => 'error',
            'message' => 'Your form has validation errors',
            'errors' => $form->getErrorsCollection(),
        ]);
    }

    /**
     * Logout endpoint
     * Type POST
     * @return array
     */
    public function logoutAction()
    {
        $token = $this->session->getAccessToken();
        $accessToken = AccessToken::findFirst((new AccessTokenQuery())->token($token['access_token'])->getParams());
        $accessToken && $accessToken->delete();
        return [
            'status' => 'OK',
            'message' => 'You was successfully logged out',
        ];
    }

    /**
     * Register endpoint
     * Type POST
     */
    public function forgotPasswordAction()
    {
    }
}
