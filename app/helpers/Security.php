<?php

namespace app\helpers;

use Phalcon\Config;
use Phalcon\Di;

/**
 * Class Security
 * @package app\helpers
 */
class Security
{
    /**
     * Generate hash for password
     * @param $password
     * @return bool|string
     */
    public static function generateHashPassword($password)
    {
        /** @var Config|\stdClass $config */
        $config = Di::getDefault()->get('config');
        return password_hash($password, $config->security->passwordAlgo, [
            'cost' => $config->security->passwordCost
        ]);
    }
}
