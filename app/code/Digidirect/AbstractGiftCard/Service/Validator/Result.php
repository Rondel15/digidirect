<?php

namespace Digidirect\AbstractGiftCard\Service\Validator;

use Magento\Framework\Phrase;

class Result implements ResultInterface
{
    /**
     * @var bool
     */
    private $_isValid;

    /**
     * @var Phrase[]
     */
    private $_failsDescription;

    /**
     * @param bool $isValid
     * @param array $failsDescription
     */
    public function __construct(
        $isValid,
        array $failsDescription = []
    ) {
        $this->_isValid = (bool)$isValid;
        $this->_failsDescription = $failsDescription;
    }

    /**
     * Returns validation result
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->_isValid;
    }

    /**
     * Returns list of fails description
     *
     * @return Phrase[]
     */
    public function getFailsDescription()
    {
        return $this->_failsDescription;
    }
}
