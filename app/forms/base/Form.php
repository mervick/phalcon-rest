<?php

namespace app\forms\base;

use Phalcon\Forms\Element;
use Phalcon\Mvc\Model\Message;

/**
 * Class Form
 * @package app\forms\base
 */
class Form extends \Phalcon\Forms\Form
{
    /**
     * Collect form errors messages by fields name and returns in the assoc array
     * @return array Errors
     */
    public function getErrorsCollection()
    {
        /** @var Element[] $elements */
        $elements = $this->getElements();

        $errorsList = [];
        foreach ($elements as $element) {
            $name = $element->getName();
            /** @var Message[] $errors */
            $errors = $this->getMessagesFor($name);

            if (!empty($errors)) {
                $errorsList[$name] = [];
                foreach ($errors as $error) {
                    $errorsList[$name][] = $error->getMessage();
                }
            }
        }

        return $errorsList;
    }
}
