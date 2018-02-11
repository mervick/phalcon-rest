<?php

namespace app\controllers\base;

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Dispatcher;

/**
 * Class RestController
 * @package app\controllers\base
 */
class RestController extends Controller
{
    /**
     * Gets application/json data and move it into $_POST array
     */
    public function beforeExecuteRoute()
    {
        if ($this->request->isPost()) {
            if (trim(explode(';', strtolower($this->request->getHeader('Content-Type')), 2)[0]) === 'application/json') {
                $data = json_decode(file_get_contents('php://input'), true);
                if ($data) {
                    $_POST = array_merge($_POST, $data);
                }
            }
        }
    }

    /**
     * Transforms returned values from actions to json data and send response
     * @param Dispatcher $dispatcher
     */
    public function afterExecuteRoute($dispatcher)
    {
        $data = $dispatcher->getReturnedValue();
        $this->response->setContentType('application/json', 'utf-8');
        $this->response->setJsonContent($data);
        $this->response->send();
    }
}
