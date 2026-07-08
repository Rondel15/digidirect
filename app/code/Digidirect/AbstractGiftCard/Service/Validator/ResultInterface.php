<?php

namespace Digidirect\AbstractGiftCard\Service\Validator;

use Magento\Framework\Phrase;

/**
 * Interface ResultInterface
 * @package Digidirect\AbstractGiftCard\Service\Validator
 * @api
 */
interface ResultInterface
{
    /**
     * Returns validation result
     *
     * @return bool
     */
    public function isValid();

    /**
     * Returns list of fails description
     *
     * @return Phrase[]
     */
    public function getFailsDescription();
}
