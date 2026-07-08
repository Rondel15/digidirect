<?php

namespace Digidirect\ExtendedCartPriceRules\Plugin\Magento\Checkout\Model;

use Digidirect\ExtendedCartPriceRules\Model\IncreaseRuleManagement;

class TotalsInformationManagement
{
    /**
     * Cart total repository.
     *
     * @var \Magento\Quote\Api\CartTotalRepositoryInterface
     */
    protected $cartTotalRepository;

    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var \Magento\SalesRule\Model\Validator
     */
    protected $validator;

    /**
     * @var IncreaseRuleManagement
     */
    protected $increaseRuleManagement;

    /**
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalRepository
     * @param \Magento\SalesRule\Model\Validator $validator
     * @param \Digidirect\ExtendedCartPriceRules\Model\IncreaseRuleManagement $increaseRuleManagement
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalRepository,
        \Magento\SalesRule\Model\Validator $validator,
        IncreaseRuleManagement $increaseRuleManagement
    ) {
        $this->cartRepository = $cartRepository;
        $this->cartTotalRepository = $cartTotalRepository;
        $this->validator = $validator;
        $this->increaseRuleManagement = $increaseRuleManagement;
    }

    /**
     * @param \Magento\Checkout\Model\TotalsInformationManagement $subject
     * @param int $cartId
     * @param \Magento\Checkout\Api\Data\TotalsInformationInterface $addressInformation
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeCalculate(
        \Magento\Checkout\Model\TotalsInformationManagement $subject,
        $cartId,
        $addressInformation
    ) {
        $this->increaseRuleManagement->processRuleByAddressInformation($cartId, $addressInformation);

        return [$cartId, $addressInformation];
    }
}
