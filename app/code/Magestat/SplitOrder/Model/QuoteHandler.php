<?php

namespace Magestat\SplitOrder\Model;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magestat\SplitOrder\Api\QuoteHandlerInterface;
use Magestat\SplitOrder\Helper\Data as HelperData;
use Magestat\SplitOrder\Api\ExtensionAttributesInterface;

/**
 * Class QuoteHandler
 * Responsible to build some methods from the quote.
 */
class QuoteHandler implements QuoteHandlerInterface
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * @var ExtensionAttributesInterface
     */
    private $extensionAttributes;

    protected $logger;
    
    /**
     * QuoteHandler constructor.
     * @param CheckoutSession $checkoutSession
     * @param HelperData $helperData
     * @param ExtensionAttributesInterface $extensionAttributes
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        HelperData $helperData,
        ExtensionAttributesInterface $extensionAttributes,
        \Psr\Log\LoggerInterface $logger,
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->helperData = $helperData;
        $this->extensionAttributes = $extensionAttributes;       
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function normalizeQuotes($quote)
    {
        if (!$this->helperData->isActive()) {
            return false;
        }
        $attributes = $this->helperData->getAttributes();
        if (empty($attributes)) {
            return false;
        }
        $groups = [];

        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($quote->getAllVisibleItems() as $item) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $item->getProduct();

            $attribute = $this->getProductAttributes($product, $attributes);
            if ($attribute === false) {
                return false;
            }
            $groups[$attribute][] = $item;
        }
        // If order have more than one different attribute values.
        if (count($groups) > 1) {
            return $groups;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getProductAttributes($product, $attributeCode)
    {
        $extensionAttribute = $this->extensionAttributes->loadValue($product, $attributeCode);
        if ($extensionAttribute !== false) {
            return $extensionAttribute;
        }
        $attributeObject = $product->getResource()->getAttribute($attributeCode);

        $attributeValue = $attributeObject->getFrontend()->getValue($product);
        if ($attributeValue instanceof \Magento\Framework\Phrase) {
            return $attributeValue->__toString();
        }
        return $attributeValue;
    }

    /**
     * @inheritdoc
     */
    public function collectAddressesData($quote)
    {
        $billing = $quote->getBillingAddress()->getData();
        unset($billing['id']);
        unset($billing['quote_id']);

        $shipping = $quote->getShippingAddress()->getData();
        unset($shipping['id']);
        unset($shipping['quote_id']);

        return [
            'payment' => $quote->getPayment()->getMethod(),
            'billing' => $billing,
            'shipping' => $shipping
        ];
    }

    /**
     * @inheritdoc
     */
    public function setCustomerData($quote, $split)
    {
        $split->setStoreId($quote->getStoreId());
        $split->setCustomer($quote->getCustomer());
        $split->setCustomerIsGuest($quote->getCustomerIsGuest());

        if ($quote->getCheckoutMethod() === CartManagementInterface::METHOD_GUEST) {
            $split->setCustomerId(null);
            $split->setCustomerEmail($quote->getBillingAddress()->getEmail());
            $split->setCustomerIsGuest(true);
            $split->setCustomerGroupId(GroupInterface::NOT_LOGGED_IN_ID);
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function populateQuote($quotes, $split, $items, $addresses, $payment, $hasDigi, $validSellers)
    {
        $this->recollectTotal($quotes, $items, $split, $addresses, $hasDigi, $validSellers);
        // Set payment method.
        $this->setPaymentMethod($split, $addresses['payment'], $payment);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function recollectTotal($quotes, $items, $quote, $addresses, $hasDigi, $validSellers)
    {
        $tax = 0.0;
        $discount = 0.0;
        $finalPrice = 0.0;

        foreach ($items as $item) {
            // Retrieve values.
            $tax += $item->getData('tax_amount');
            $discount += $item->getData('discount_amount');

            $finalPrice += ($item->getPrice() * $item->getQty());
        }

        // Set addresses.
        $quote->getBillingAddress()->setData($addresses['billing']);
        $quote->getShippingAddress()->setData($addresses['shipping']);

        $shipping = $this->shippingAmount($quotes, $quote, $hasDigi, $validSellers);
        // Recollect totals into the quote.
        foreach ($quote->getAllAddresses() as $address) {
            // Build grand total.
            $grandTotal = (($finalPrice + $shipping + $tax) - $discount);

            $address->setBaseSubtotal($finalPrice);
            $address->setSubtotal($finalPrice);
            $address->setDiscountAmount($discount);
            $address->setTaxAmount($tax);
            $address->setBaseTaxAmount($tax);
            $address->setBaseGrandTotal($grandTotal);
            $address->setGrandTotal($grandTotal);
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function shippingAmount($quotes, $quote, $hasDigi, $validSellers, $total = 0.0)
    {
        // Add shipping amount if product is not virtual.
        if ($quote->hasVirtualItems() === true) {
            return $total;
        }
        $shippingTotals = $quote->getShippingAddress()->getShippingAmount();

        // If not, set shipping to one order only.
        if (!$this->helperData->getShippingSplit()) {
            static $process = 1;

            if ($process > 1) {
                // Set zero price to next orders.
                $quote->getShippingAddress()->setShippingAmount($total);
                return $total;
            }
            $process ++;

            return $shippingTotals;
        }
        
        $groups = [];
        
        if ($shippingTotals > 0) {
            // Divide shipping to each order.
            
            $excludeSeller = ['LatestBuy'];
            $hasExcludedSeller = false;

            if (!$this->helperData->isActive()) {
                return false;
            }
            $attributes = $this->helperData->getAttributes();
            if (empty($attributes)) {
                return false;
            }

            foreach ($quote->getAllVisibleItems() as $visibleItem) {
                $product = $visibleItem->getProduct();
                $this->logger->info("productName, " . $product->getName());
                $attribute = $this->getProductAttributes($product, $attributes);
                $this->logger->info("attribute, " . $attribute);
                if ($attribute === false) {
                    return false;
                }
                $groups[$attribute][] = $visibleItem;
                if ($attribute == "digiDirect") {
                    $total = $shippingTotals - ($validSellers * 8.95);
                } else {
                    $total = 8.95;
                }
                if (in_array($attribute, $excludeSeller)) {
                    $total = 0.0;
                }
                if ($attribute == false) {
                    return false;
                }
            }
            
            $quote->getShippingAddress()->setShippingAmount($total);
        }
        return $total;
    }

    /**
     * @inheritdoc
     */
    public function setPaymentMethod($split, $payment, $paymentMethod)
    {
        $split->getPayment()->setMethod($payment);

        if ($paymentMethod) {
            $split->getPayment()->setQuote($split);
            $data = $paymentMethod->getData();
            $split->getPayment()->importData($data);
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function defineSessions($split, $order, $orderIds)
    {
        $this->checkoutSession->setLastQuoteId($split->getId());
        $this->checkoutSession->setLastSuccessQuoteId($split->getId());
        $this->checkoutSession->setLastOrderId($order->getId());
        $this->checkoutSession->setLastRealOrderId($order->getIncrementId());
        $this->checkoutSession->setLastOrderStatus($order->getStatus());
        $this->checkoutSession->setOrderIds($orderIds);

        return $this;
    }
}
