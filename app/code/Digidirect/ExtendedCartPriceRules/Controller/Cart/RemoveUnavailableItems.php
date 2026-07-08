<?php

namespace Digidirect\ExtendedCartPriceRules\Controller\Cart;

use Digidirect\ExtendedCartPriceRules\Model\ResourceModel\ExtendedCartPriceRule;
use Digidirect\ExtendedCartPriceRules\Model\Rule\Action\Discount\RemoveCartItem;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteRepository;

class RemoveUnavailableItems extends \Magento\Framework\App\Action\Action
{
    /**
     * @var ExtendedCartPriceRule
     */
    protected $extendedCartPriceRule;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * RemoveUnavailableItems constructor.
     *
     * @param Context $context
     * @param ExtendedCartPriceRule $extendedCartPriceRule
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param QuoteRepository $quoteRepository
     */
    public function __construct(
        Context $context,
        ExtendedCartPriceRule $extendedCartPriceRule,
        \Magento\Checkout\Model\Session $checkoutSession,
        QuoteRepository $quoteRepository
    ) {
        parent::__construct($context);
        $this->extendedCartPriceRule = $extendedCartPriceRule;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setUrl($this->_redirect->getRefererUrl());

        $items = $this->extendedCartPriceRule->getQuoteItemsAppliedToRuleAction(RemoveCartItem::SIMPLE_ACTION);
        $quote = $this->checkoutSession->getQuote();

        $removedItems = [];
        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($items as $item) {
            $quote->removeItem($item->getItemId());
            $removedItems[$item->getItemId()] = $item->getName();
        }
        if ($removedItems) {
            $quote->collectTotals();
            $this->quoteRepository->save($quote);
        }

        if ($this->getRequest()->isAjax()) {
            $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $result->setData(array_keys($removedItems));
        } else {
            if (strpos($this->_redirect->getRefererUrl(), 'checkout/cart') === false) {
                $this->messageManager->addNoticeMessage(
                    'The following product(s) has been removed from your cart: ' . implode(', ', $removedItems)
                );
            }
        }
        return $result;
    }
}
