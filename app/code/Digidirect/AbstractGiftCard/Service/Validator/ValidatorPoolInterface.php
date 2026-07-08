<?php

namespace Digidirect\AbstractGiftCard\Service\Validator;

use Magento\Framework\Exception\NotFoundException;

/**
 * Interface ValidatorPoolInterface
 * @package Digidirect\AbstractGiftCard\Service\Validator
 * @api
 */
interface ValidatorPoolInterface
{
    /**
     * Returns configured validator
     *
     * @param string $code
     * @return \Digidirect\AbstractGiftCard\Service\Validator\ValidatorInterface
     * @throws NotFoundException
     */
    public function get($code);
}
