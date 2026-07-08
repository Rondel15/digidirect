<?php

namespace Digidirect\ExtendedCartPriceRules\Model;

/**
 * Class RuleManagement
 *
 * @package Digidirect\ExtendedCartPriceRules\Model
 */
class RuleManagement implements \Digidirect\ExtendedCartPriceRules\Api\RuleManagementInterface
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Digidirect\ExtendedCartPriceRules\Helper\Data
     */
    protected $helper;

    /**
     * RuleManagement constructor.
     *
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Digidirect\ExtendedCartPriceRules\Helper\Data $helper
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Digidirect\ExtendedCartPriceRules\Helper\Data $helper
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->helper = $helper;
    }

    /**
     * @param null|int $cartId
     * @return array
     */
    public function getPaymentMethodLimit($cartId = null)
    {
        $quote = null;
        if ($cartId) {
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $this->quoteRepository->get($cartId);
        }

        /**
         * @var array $output
         */
        $output = $this->helper->getAvailableMethods($quote);

        return $output;
    }
}
