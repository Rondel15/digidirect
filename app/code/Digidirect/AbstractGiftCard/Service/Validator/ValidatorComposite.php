<?php

namespace Digidirect\AbstractGiftCard\Service\Validator;

use Magento\Framework\ObjectManager\TMap;
use Magento\Framework\ObjectManager\TMapFactory;
use Digidirect\AbstractGiftCard\Service\Validator\ResultInterfaceFactory;

/**
 * Class ValidatorComposite
 * @package Digidirect\AbstractGiftCard\Service\Validator
 * @api
 */
class ValidatorComposite extends AbstractValidator
{
    /**
     * @var ValidatorInterface[] | TMap
     */
    private $_validators;

    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param TMapFactory $tmapFactory
     * @param array $validators
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        TMapFactory $tmapFactory,
        array $validators = []
    ) {
        $this->_validators = $tmapFactory->create(
            [
                'array' => $validators,
                'type' => ValidatorInterface::class
            ]
        );
        parent::__construct($resultFactory);
    }

    /**
     * Performs domain level validation for business object
     *
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $isValid = true;
        $failsDescriptionAggregate = [];
        foreach ($this->_validators as $validator) {
            $result = $validator->validate($validationSubject);
            if (!$result->isValid()) {
                $isValid = false;
                $failsDescriptionAggregate = array_merge(
                    $failsDescriptionAggregate,
                    $result->getFailsDescription()
                );
            }
        }

        return $this->createResult($isValid, $failsDescriptionAggregate);
    }
}
