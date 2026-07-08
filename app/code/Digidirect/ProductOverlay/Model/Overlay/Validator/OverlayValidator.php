<?php
namespace Digidirect\ProductOverlay\Model\Overlay\Validator;

/**
 * Class OverlayValidator
 * @package Digidirect\ProductOverlay\Model\Overlay\Validator
 */
class OverlayValidator implements \Zend_Validate_Interface
{
    /**
     * @var array
     */
    protected $validatorsFactory;

    /**
     * @var array
     */
    protected $fieldsToValidate;

    /**
     * OverlayValidator constructor.
     * @param array $validatorsFactory
     * @param array $fieldsToValidate
     */
    public function __construct(array $validatorsFactory = [], array $fieldsToValidate = [])
    {
        $this->validatorsFactory = $validatorsFactory;
        $this->fieldsToValidate = $fieldsToValidate;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        $allMessages = [];
        if (!empty($this->validatorsFactory)) {
            foreach ($this->validatorsFactory as $key => $validator) {
                $messages = $validator->getMessages();
                foreach ($messages as $msg) {
                    $allMessages[] = $msg;
                }
            }
        }

        return $allMessages;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function isValid($value)
    {
        $notValid = [];
        if (!empty($this->validatorsFactory)) {
            foreach ($this->validatorsFactory as $key => $validator) {
                if (!$validator->isValid($value)) {
                    $notValid[] = $key;
                }
            }
        }

        return empty($notValid);
    }
}
