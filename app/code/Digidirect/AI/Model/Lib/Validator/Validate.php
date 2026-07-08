<?php
namespace Digidirect\AI\Model\Lib\Validator;

use Zend_Validate_Interface as Validator;

class Validate extends \Zend_Validate
{
    /**
     * @param Validator[] $validators
     */
    public function __construct(array $validators = [])
    {
        foreach ($validators as $validator) {
            $this->addValidator($validator);
        }
    }
}
