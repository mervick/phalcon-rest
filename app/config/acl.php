<?php

return [
    // Acl role ALL, means all users
    ACL_ALL => [
        'name' => 'all',
        'description' => 'All users',
        'resources' => [ // acl resources
            // @TODO add resources
//             // Example:
//            'user' => [
//                'allow' => 'read',
//                'deny' => [
//                    'create',
//                    'update',
//                    'delete',
//                ],
//            ],
        ],
    ],

    // Acl role GUEST, means only guests
    ACL_GUEST => [
        'name' => 'guest',
        'description' => 'Guest',
        'resources' => [ // acl resources
            // @TODO add resources
        ],
    ],

    // Acl role USER, means logged in user
    ACL_USER => [
        'name' => 'user',
        'description' => 'Logged in user',
        'resources' => [ // acl resources
            // @TODO add resources
        ],
    ],
];
