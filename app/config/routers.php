<?php

return [
    // IndexController
    'index' => [
        'index' => [
            'GET' => [
                'action' => 'index',
                'allow' => [ACL_USER],
                'deny' => [ACL_GUEST],
            ],
        ],
    ],

    // AuthController
    'auth' => [
        'login' => [
            'POST' => [
                'action' => 'login',
                'allow' => [ACL_GUEST],
                'deny' => [ACL_USER],
            ],
        ],
        'register' => [
            'POST' => [
                'action' => 'register',
                'allow' => [ACL_GUEST],
                'deny' => [ACL_USER],
            ],
        ],
        'logout' => [
            'POST' => [
                'action' => 'logout',
                'allow' => [ACL_USER],
                'deny' => [ACL_GUEST],
            ],
        ],
//        'forgot' => [
//            'POST' => [
//                'action' => 'forgotPassword',
//                'allow' => [ACL_GUEST],
//                'deny' => [ACL_USER],
//            ],
//        ],
    ],
];
