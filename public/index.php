<?php

//error_reporting(E_ALL ^ E_DEPRECATED);
//ini_set('display_errors', 'On');

// Disable xdebug extension for avoid format output
if (function_exists('xdebug_disable')) {
    xdebug_disable();
}

require __DIR__ . '/../app/bootstrap.php';
$config = require APP_DIR . '/config/config.php';

try {

    $app = new \Phalcon\Mvc\Application();
    $app->setDI(Phalcon\Di::getDefault());
    $app->useImplicitView(false);
    $app->handle();
} catch (\Phalcon\Http\Response\Exception $e) {
    $data = [
        'status' => 'error',
        'message' => $e->getMessage(),
    ];
    if (DEBUG) {
        $data['trace'] = explode("\n", $e->getTraceAsString());
    }
    $app->response
        ->setStatusCode($e->getCode(), $e->getMessage())
        ->setJsonContent($data)
        ->send();
} catch (\Phalcon\Http\Request\Exception $e) {
    $data = [
        'status' => 'error',
        'message' => $e->getMessage(),
    ];
    if (DEBUG) {
        $data['trace'] = explode("\n", $e->getTraceAsString());
    }
    $app->response
        ->setStatusCode(400, 'Bad request')
        ->setJsonContent($data)
        ->send();
} catch (\Exception $e) {
    $data = [
        'status' => 'error',
        'message' => $e->getMessage(),
    ];
    if (DEBUG) {
        $data['trace'] = explode("\n", $e->getTraceAsString());
    }
    $app->response
        ->setStatusCode(500, 'Internal Server Error')
        ->setJsonContent($data)
        ->send();
}
