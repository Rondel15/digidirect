<?php

namespace Digidirect\AbstractGiftCard\Service\Validator;

/**
 * Interface ValidatorInterface
 * @package Digidirect\AbstractGiftCard\Service\Validator
 * @api
 */
interface ValidatorInterface
{
    /**
     * Performs domain-related validation for business object
     *
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject);
}
