<?php

return new Phalcon\Config([
    'acl' => require 'acl.php',
    'routes' => require 'routers.php',

    'application' => [
        'name' => 'Test',
        'defaultNamespace' => 'app\\controllers',
        'defaultController' => 'index',
        'defaultAction' => 'GET auth.login',
    ],

    'oauth' => [
        'user_table' => 'user',
        'user_table' => 'user',
    ],

    'database' => [
        'adapter' => 'Mysql',
        'host' => '127.0.0.1',
        'port' => 3306,
        'username' => 'root',
        'password' => 'admin',
        'dbname' => 'test',
        'charset' => 'utf8mb4'
    ],

    'security' => [
        'passwordAlgo' => PASSWORD_DEFAULT,
        'passwordCost' => 10,
        'passwordSalt' => '`{]Y3z9;jYi',
        'passwordRegex' => '/(?=^.{8,}$)(?=.*\d)(?=.*[!@#\$%\^&\*]+)(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$/',
        'recaptcha' => [
            'publicKey' => '6LetqEUUAAAAAKVr-36FxEcXy_Wng1ykig-kMyNo',
            'secretKey' => '6LetqEUUAAAAALuUJpqeMbSFOKdsK6FJHh8So-xR',
        ],
    ],

    'debug' => [
        'error_reporting' => DEBUG ? E_ALL ^ E_DEPRECATED : 0,
        'display_errors' => DEBUG ? 'On' : 'Off',
    ],
]);
