<?php
namespace Digidirect\AI\Model\Lib\Validator;

use Valitron\Validator as ValitronValidator;
use Valitron\ValidatorFactory as ValitronValidatorFactory;

class InputValidator implements \Zend_Validate_Interface
{
    /**
     * @var ValitronValidatorFactory
     */
    protected $valitronValidatorFactory;

    /**
     * @var array
     */
    protected $messages = [];

    /**
     * @var array
     */
    protected $inputFormat = [];

    /**
     * @var array
     */
    protected $callbackRules = [];

    /**
     * BeforeSave constructor.
     * @param ValitronValidatorFactory $valitronValidatorFactory
     * @param array $inputFormat
     * @param array $callbackRules
     */
    public function __construct(
        ValitronValidatorFactory $valitronValidatorFactory,
        array $inputFormat = [],
        array $callbackRules = []
    ) {
        $this->valitronValidatorFactory = $valitronValidatorFactory;
        $this->inputFormat = $inputFormat;
        $this->callbackRules = $callbackRules;
    }

    /**
     * @param array $data
     * @return bool
     */
    public function isValid($data)
    {
        $this->messages = [];
        /** @var ValitronValidator $validation */
        $validation = $this->valitronValidatorFactory->create([
            'data' => $data
        ]);

        foreach ($this->inputFormat as $field => $rule) {
            if (is_string($rule)) {
                $rule = explode(',', $rule);
            }
            $validation->mapFieldRules($field, $rule);
        }

        foreach ($this->callbackRules as $fieldName => $callbackData) {
            $validation->rule(function ($field, $value) use ($callbackData) {
                return call_user_func(array_values($callbackData), $field, $value);
            }, $fieldName);
        }

        if (!$validation->validate()) {
            $errors = $validation->errors();
            foreach ($errors as $error) {
                $this->messages = array_merge($this->messages, $error);
            }
            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
