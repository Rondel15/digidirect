<?php

namespace Digidirect\AbstractGiftCard\Service\Validator;

/**
 * Class AbstractValidator
 * @package Digidirect\AbstractGiftCard\Service\Validator
 * @api
 */
abstract class AbstractValidator implements ValidatorInterface
{
    /**
     * @var ResultInterfaceFactory
     */
    private $_resultInterfaceFactory;

    /**
     * @param ResultInterfaceFactory $resultFactory
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory
    ) {
        $this->_resultInterfaceFactory = $resultFactory;
    }

    /**
     * Factory method
     *
     * @param bool $isValid
     * @param array $fails
     * @return ResultInterface
     */
    protected function _createResult($isValid, array $fails = [])
    {
        return $this->_resultInterfaceFactory->create(
            [
                'isValid' => (bool)$isValid,
                'failsDescription' => $fails
            ]
        );
    }
}
