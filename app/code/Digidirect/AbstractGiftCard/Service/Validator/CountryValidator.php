<?php

namespace Digidirect\AbstractGiftCard\Service\Validator;

use Magento\Framework\Exception\NotFoundException;
use Digidirect\AbstractGiftCard\Service\ConfigInterface;
use Digidirect\AbstractGiftCard\Service\Validator\ResultInterfaceFactory;

/**
 * Class CountryValidator
 * @package Digidirect\AbstractGiftCard\Service\Validator
 * @api
 */
class CountryValidator extends AbstractValidator
{
    /**
     * @var \Digidirect\AbstractGiftCard\Service\ConfigInterface
     */
    private $_config;

    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param \Digidirect\AbstractGiftCard\Service\ConfigInterface $config
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        ConfigInterface $config
    ) {
        $this->_config = $config;
        parent::__construct($resultFactory);
    }

    /**
     * @param array $validationSubject
     * @return bool
     * @throws NotFoundException
     * @throws \Exception
     */
    public function validate(array $validationSubject)
    {
        $isValid = true;
        $storeId = $validationSubject['storeId'];

        if ((int)$this->_config->getValue('allowspecific', $storeId) === 1) {
            $availableCountries = explode(
                ',',
                $this->_config->getValue('specificcountry', $storeId)
            );

            if (!in_array($validationSubject['country'], $availableCountries)) {
                $isValid =  false;
            }
        }

        return $this->createResult($isValid);
    }
}
