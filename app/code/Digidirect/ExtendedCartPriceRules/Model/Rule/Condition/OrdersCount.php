<?php

namespace Digidirect\ExtendedCartPriceRules\Model\Rule\Condition;

use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Quote\Model\Quote;
use Digidirect\ExtendedCartPriceRules\Model\ResourceModel\Sales\Order as OrderResource;
use Digidirect\ExtendedCartPriceRules\Helper\Config as ConfigHelper;
use Magento\Checkout\Model\Session as CheckoutSession;

class OrdersCount extends AbstractCondition
{
    /**
     * @var OrderResource
     */
    protected $_orderResource;

    /**
     * @var ConfigHelper
     */
    protected $_configHelper;

    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;

    /**
     * OrdersCount constructor.
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param OrderResource $orderResource
     * @param ConfigHelper $configHelper
     * @param CheckoutSession $customerSession
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        OrderResource $orderResource,
        ConfigHelper $configHelper,
        CheckoutSession $customerSession,
        array $data = []
    ) {
        $this->_configHelper = $configHelper;
        $this->_orderResource = $orderResource;
        $this->_checkoutSession = $customerSession;
        parent::__construct($context, $data);
    }

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            'previous_customer_orders' => __('Previous Customer Orders'),
        ];

        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * Get attribute element
     *
     * @return $this
     */
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    /**
     * Get input type
     *
     * @return string
     */
    public function getInputType()
    {
        return 'numeric';
    }

    /**
     * Get value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        return 'text';
    }

    /**
     * Validate Address Rule Condition
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $quote = $model;
        if (!$quote instanceof Quote) {
            $quote = $model->getQuote();
        }

        $customerId = $quote->getCustomerId();
        $customerEmail = $customerId ?
            $quote->getCustomerEmail() : $this->_checkoutSession->getData('last_checked_email');

        if (!$customerEmail) {
            return false;
        }

        $ordersCount = $this->_orderResource->getCustomerOrdersCount(
            $customerId,
            $customerEmail,
            $this->_configHelper->getOrderStatusesForOrdersCount($quote->getStoreId())
        );

        $model->setData('previous_customer_orders', $ordersCount);

        return parent::validate($model);
    }
}
