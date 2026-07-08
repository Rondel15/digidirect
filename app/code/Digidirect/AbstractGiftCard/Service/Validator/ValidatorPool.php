<?php

namespace Digidirect\AbstractGiftCard\Service\Validator;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\ObjectManager\TMap;
use Magento\Framework\ObjectManager\TMapFactory;

/**
 * Class ValidatorPool
 * @package Digidirect\AbstractGiftCard\Service\Validator
 * @api
 */
class ValidatorPool implements \Digidirect\AbstractGiftCard\Service\Validator\ValidatorPoolInterface
{
    /**
     * @var ValidatorInterface[] | TMap
     */
    private $_validators;

    /**
     * @param TMapFactory $tmapFactory
     * @param array $validators
     */
    public function __construct(
        TMapFactory $tmapFactory,
        array $validators = []
    ) {
        $this->_validators = $tmapFactory->create(
            [
                'array' => $validators,
                'type' => ValidatorInterface::class
            ]
        );
    }

    /**
     * Returns configured validator
     *
     * @param string $code
     * @return ValidatorInterface
     * @throws NotFoundException
     */
    public function get($code)
    {
        if (!isset($this->_validators[$code])) {
            throw new NotFoundException(__('Validator for field %1 does not exist.', $code));
        }

        return $this->_validators[$code];
    }
}
