<?php

// defines app paths
define('APP_DIR', __DIR__);
define('ROOT_DIR', dirname(__DIR__));
define('PUBLIC_DIR', ROOT_DIR . '/public');

// defines acl roles
define('ACL_ALL', 'all');
define('ACL_USER', 'user');
define('ACL_GUEST', 'guest');

// set debug on development environment
//define('DEBUG', true) or
define('DEBUG', false);
