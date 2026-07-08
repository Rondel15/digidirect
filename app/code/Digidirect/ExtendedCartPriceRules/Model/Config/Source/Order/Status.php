<?php

namespace Digidirect\ExtendedCartPriceRules\Model\Config\Source\Order;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    const UNDEFINED_OPTION_LABEL = '-- Please Select --';

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;

    /**
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     */
    public function __construct(\Magento\Sales\Model\Order\Config $orderConfig)
    {
        $this->_orderConfig = $orderConfig;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $statuses = $this->_orderConfig->getStatuses();
        asort($statuses);
        $options = [];
        foreach ($statuses as $code => $label) {
            $options[] = ['value' => $code, 'label' => $label];
        }
        return $options;
    }
}
