<?php

namespace app\forms\validation;

use Phalcon\Config;
use Phalcon\Di;
use Phalcon\Http\Request;
use Phalcon\Validation;
use Phalcon\Validation\Message;
use Phalcon\Validation\Validator;
use Phalcon\Validation\ValidatorInterface;

/**
 * Class ReCaptcha
 * @package app\forms\validation
 */
class ReCaptcha extends Validator implements ValidatorInterface
{
    /**
     * Executes the validation
     * @param Validation $validation
     * @param string $field
     * @return bool|void
     */
    public function validate(Validation $validation, $field)
    {
        $value = $validation->getValue($field);

        if (!$this->validateResponse($value)) {
            $message = $this->getOption("message") ?: 'ReCaptcha validation fail';
            $validation->appendMessage(new Message($message, $field, "ReCaptcha"));
            return false;
        }
    }

    /**
     * Validate response
     * @param string $response
     * @return bool
     */
    protected function validateResponse($response)
    {
        /** @var Config|\stdClass $config */
        $config = Di::getDefault()->get('config');
        /** @var Request $request */
        $request = Di::getDefault()->get('request');
        
        $secretKey = $config->security->recaptcha->secretKey;
        $remoteIP = $request->getClientAddress();

        $url = "https://www.google.com/recaptcha/api/siteverify?secret={$secretKey}&response={$response}&remoteip={$remoteIP}";
        $data = json_decode(file_get_contents($url), true);
        
        return $data && $data['success'] ?? false; 
    }
}
