<?php
namespace Digidirect\ExtendedShippingRates\Model\Rule\Condition;

class Address extends \Magento\SalesRule\Model\Rule\Condition\Address
{
    /**
     * @var \Digidirect\ExtendedShippingRates\Model\ResourceModel\Zone\CollectionFactory
     */
    protected $zoneCollectionFactory;

    /**
     * Address constructor.
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Directory\Model\Config\Source\Country $directoryCountry
     * @param \Magento\Directory\Model\Config\Source\Allregion $directoryAllregion
     * @param \Magento\Shipping\Model\Config\Source\Allmethods $shippingAllmethods
     * @param \Magento\Payment\Model\Config\Source\Allmethods $paymentAllmethods
     * @param \Digidirect\ExtendedShippingRates\Model\ResourceModel\Zone\CollectionFactory $zoneCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Directory\Model\Config\Source\Country $directoryCountry,
        \Magento\Directory\Model\Config\Source\Allregion $directoryAllregion,
        \Magento\Shipping\Model\Config\Source\Allmethods $shippingAllmethods,
        \Magento\Payment\Model\Config\Source\Allmethods $paymentAllmethods,
        \Digidirect\ExtendedShippingRates\Model\ResourceModel\Zone\CollectionFactory $zoneCollectionFactory,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $directoryCountry,
            $directoryAllregion,
            $shippingAllmethods,
            $paymentAllmethods,
            $data
        );
        $this->zoneCollectionFactory = $zoneCollectionFactory;
    }

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            'base_subtotal' => __('Subtotal'),
            'base_subtotal_after_discount' => __('Subtotal with Discount'),
            'total_qty' => __('Total Items Quantity'),
            'weight' => __('Total Weight'),
            'postcode' => __('Shipping Postcode'),
            'region' => __('Shipping Region'),
            'region_id' => __('Shipping State/Province'),
            'country_id' => __('Shipping Country'),
            'zone_id' => __('Shipping Zone')
        ];

        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * Get input type
     *
     * @return string
     */
    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'base_subtotal':
            case 'base_subtotal_after_discount':
            case 'weight':
            case 'total_qty':
                return 'numeric';
            case 'country_id':
            case 'region_id':
            case 'zone_id':
                return 'select';
        }
        return 'string';
    }

    /**
     * Get value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        switch ($this->getAttribute()) {
            case 'country_id':
            case 'region_id':
            case 'zone_id':
                return 'select';
        }
        return 'text';
    }

    /**
     * Get value select options
     *
     * @return array|mixed
     */
    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            switch ($this->getAttribute()) {
                case 'country_id':
                    $options = $this->_directoryCountry->toOptionArray();
                    break;

                case 'region_id':
                    $options = $this->_directoryAllregion->toOptionArray();
                    break;

                case 'zone_id':
                    /** @var \Digidirect\ExtendedShippingRates\Model\ResourceModel\Zone\Collection $zonesCollection */
                    $zonesCollection = $this->zoneCollectionFactory->create();
                    $options = $zonesCollection->toOptionArray();
                    break;

                default:
                    $options = [];
            }
            $this->setData('value_select_options', $options);
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
        /** @var \Magento\Quote\Model\Quote\Address|\Magento\Framework\Model\AbstractModel $address */
        $address = $model;
        if (!$address instanceof \Magento\Quote\Model\Quote\Address) {
            if ($model->getQuote()->isVirtual()) {
                $address = $model->getQuote()->getBillingAddress();
            } else {
                $address = $model->getQuote()->getShippingAddress();
            }
        }

        if ('base_subtotal_after_discount' == $this->getAttribute() && !$address->hasData($this->getAttribute())) {
            $baseSubtotalAfterDiscount = $this->calculateBaseSubtotalAfterDiscount($address);
            $address->setData('base_subtotal_after_discount', $baseSubtotalAfterDiscount);
        }

        if ($this->getAttribute() == 'zone_id' && !$address->hasData('zone_id')) {
            $this->addZoneToAddress($address);
            if (!$address->hasData('zone_id')) {
                return false;
            }
        }

        return parent::validate($address);
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return float
     */
    protected function calculateBaseSubtotalAfterDiscount(\Magento\Quote\Model\Quote\Address $address)
    {
        $baseSubtotalAfterDiscount = $address->getBaseSubtotalWithDiscount();

        return $baseSubtotalAfterDiscount;
    }

    /**
     * Detect valid zone for the address
     * Store valid zone id in address data
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return void
     */
    protected function addZoneToAddress($address)
    {
        /** @var \Digidirect\ExtendedShippingRates\Model\ResourceModel\Zone\Collection $zoneCollection */
        $zoneCollection = $this->zoneCollectionFactory->create();
        $zoneCollection->addStoreFilter($address->getQuote()->getStore()->getId())
            ->setOrder('priority', \Magento\Framework\Data\Collection\AbstractDb::SORT_ORDER_ASC);

        /** @var \Digidirect\ExtendedShippingRates\Model\Zone $zone */
        foreach ($zoneCollection as $zone) {
            if ($zone->validate($address)) {
                $address->setData('zone_id', $zone->getId());
            }
        }
    }
}
