<?php

namespace app\models;

use Phalcon\Mvc\Model\Criteria;

/**
 * Class AccessTokenQuery
 * @package app\models
 */
class AccessTokenQuery extends Criteria
{
    /**
     * Select access token by token
     * @param string $token
     * @return $this
     */
    public function token($token)
    {
        $this->andWhere('access_token=:access_token:', [
            'access_token' => $token,
        ]);
        return $this;
    }
}
