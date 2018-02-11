<?php

namespace app\controllers;

use app\controllers\base\RestController;

/**
 * Class IndexController
 * @package app\controllers
 */
class IndexController extends RestController
{
    /**
     * Returns content for logged in users
     * @return array
     */
    public function indexAction()
    {
        return [
            'status' => 'OK',
            'content' => file_get_contents(ROOT_DIR . '/files/content.html')
        ];
    }
}
