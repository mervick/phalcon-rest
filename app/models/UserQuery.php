<?php

namespace app\models;

use Phalcon\Mvc\Model\Criteria;

/**
 * Class UserQuery
 * @package app\models
 */
class UserQuery extends Criteria
{
    /**
     * Select user by email
     * @param string $email
     * @return $this
     */
    public function email($email)
    {
        $this->andWhere('email=:email:', [
            'email' => $email
        ]);
        return $this;
    }

    /**
     * Select user by username
     * @param string $name
     * @return $this
     */
    public function name($name)
    {
        $this->andWhere('name=:name:', [
            'name' => $name
        ]);
        return $this;
    }
}
