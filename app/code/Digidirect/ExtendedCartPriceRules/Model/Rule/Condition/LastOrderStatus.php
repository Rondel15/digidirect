<?php

namespace Digidirect\ExtendedCartPriceRules\Model\Rule\Condition;

use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order\Config as OrderConfig;
use Digidirect\ExtendedCartPriceRules\Model\ResourceModel\Sales\Order as OrderResource;
use Magento\Checkout\Model\Session as CheckoutSession;

class LastOrderStatus extends AbstractCondition
{
    /**
     * @var OrderResource
     */
    protected $_orderResource;

    /**
     * @var OrderConfig
     */
    protected $_orderConfig;

    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;

    /**
     * LastOrderStatus constructor.
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param OrderResource $orderResource
     * @param OrderConfig $orderConfig
     * @param CheckoutSession $customerSession
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        OrderResource $orderResource,
        OrderConfig $orderConfig,
        CheckoutSession $customerSession,
        array $data = []
    ) {
        $this->_orderResource = $orderResource;
        $this->_orderConfig = $orderConfig;
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
            'last_order_status' => __('Previous Customer Order Status'),
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
        return 'select';
    }

    /**
     * Get value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        return 'select';
    }

    /**
     * Retrieve select option values
     *
     * @return array
     */
    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            $statuses = $this->_orderConfig->getStatuses();
            asort($statuses);
            $selectOptions = [];
            foreach ($statuses as $code => $label) {
                $selectOptions[] = [
                    'label' => $label,
                    'value' => $code,
                ];
            }
            $this->setData('value_select_options', $selectOptions);
        }
        return $this->getData('value_select_options');
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

        $status = $this->_orderResource->getLastCustomersOrderStatus(
            $customerId,
            $customerEmail
        );

        $model->setData('last_order_status', $status);

        return parent::validate($model);
    }
}
