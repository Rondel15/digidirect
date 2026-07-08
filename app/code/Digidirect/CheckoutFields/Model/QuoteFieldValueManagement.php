<?php

namespace Digidirect\CheckoutFields\Model;

use Digidirect\CheckoutFields\Api\Data\OrderFieldValueInterface;
use Digidirect\CheckoutFields\Api\OrderFieldValueRepositoryInterface;
use Digidirect\CheckoutFields\Api\QuoteFieldValueManagementInterface;
use Digidirect\CheckoutFields\Api\QuoteFieldValueRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartExtension;
use Magento\Quote\Api\Data\CartExtensionFactory;
use Magento\Quote\Api\Data\CartExtensionInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class QuoteFieldValueRepository
 *
 * @package Digidirect\CheckoutFields\Model
 */
class QuoteFieldValueManagement implements QuoteFieldValueManagementInterface
{
    /**
     * @var CartExtensionFactory
     */
    protected $cartExtensionFactory;

    /**
     * @var QuoteFieldValueRepositoryInterface
     */
    protected $quoteFieldValueRepository;

    /**
     * @var OrderFieldValueRepositoryInterface
     */
    protected $orderFieldValueRepository;

    /**
     * QuoteFieldValueManagement constructor.
     *
     * @param QuoteFieldValueRepositoryInterface $quoteFieldValueRepository
     * @param OrderFieldValueRepositoryInterface $orderFieldValueRepository
     * @param CartExtensionFactory               $cartExtensionFactory
     */
    public function __construct(
        QuoteFieldValueRepositoryInterface $quoteFieldValueRepository,
        OrderFieldValueRepositoryInterface $orderFieldValueRepository,
        CartExtensionFactory $cartExtensionFactory
    ) {
        $this->quoteFieldValueRepository = $quoteFieldValueRepository;
        $this->orderFieldValueRepository = $orderFieldValueRepository;
        $this->cartExtensionFactory = $cartExtensionFactory;
    }

    /**
     * {@inheritDoc}
     * @throws LocalizedException
     */
    public function saveToQuoteFromExtensionAttributes(CartInterface $quote, $reSave = true)
    {
        $quoteFieldValues = $this->getCustomFieldsFromExtensionAttributes($quote);

        if (!$quoteFieldValues) {
            return false;
        }

        return $this->quoteFieldValueRepository->saveToQuote($quote, $quoteFieldValues, $reSave);
    }

    /**
     * {@inheritDoc}
     * @throws LocalizedException
     */
    public function saveToQuoteFromOrder(CartInterface $quote, OrderInterface $order, $reSave = true)
    {
        $orderFieldValues = $this->orderFieldValueRepository->getListByOrderId($order->getEntityId());

        $params = [];
        /** @var OrderFieldValueInterface $orderFieldValue */
        foreach ($orderFieldValues as $orderFieldValue) {
            //TODO get rid of serialize/unserialize there
            $params[$orderFieldValue->getFieldId()] = unserialize($orderFieldValue->getValue());
        }

        return $this->quoteFieldValueRepository->saveToQuote($quote, $params, $reSave);
    }

    /**
     * {@inheritDoc}
     */
    public function getCustomFieldsFromExtensionAttributes(CartInterface $quote)
    {
        $cartExtension = $this->getQuoteExtension($quote);
        if (!$cartExtension || !$cartExtension->getQuoteFieldValues()) {
            return [];
        }

        return $cartExtension->getQuoteFieldValues();
    }

    /**
     * @param CartInterface $quote
     *
     * @return null|CartExtension|CartExtensionInterface
     */
    public function getQuoteExtension(CartInterface $quote)
    {
        $quoteExtension = $quote->getExtensionAttributes();
        if ($quoteExtension === null) {
            $quoteExtension = $this->cartExtensionFactory->create();
        }

        return $quoteExtension;
    }
}
