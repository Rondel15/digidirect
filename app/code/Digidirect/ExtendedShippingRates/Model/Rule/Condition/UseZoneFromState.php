<?php

namespace Digidirect\ExtendedShippingRates\Model\Rule\Condition;

use Digidirect\ExtendedShippingRates\Api\Data\ZoneInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Quote\Model\Quote;
use Magento\Rule\Model\Condition\AbstractCondition;

/**
 * Class UseZoneFromState
 *
 * @package Digidirect\ExtendedShippingRates\Model\Rule\Condition
 */
class UseZoneFromState extends AbstractCondition
{
    const ATTRIBUTE_NAME = 'use_zone_from_state';

    /**
     * @var \Digidirect\ExtendedShippingRates\Model\ResourceModel\Zone\CollectionFactory
     */
    protected $zoneCollectionFactory;

    /**
     * UseZoneFromState constructor.
     *
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Digidirect\ExtendedShippingRates\Model\ResourceModel\Zone\CollectionFactory $zoneCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Digidirect\ExtendedShippingRates\Model\ResourceModel\Zone\CollectionFactory $zoneCollectionFactory,
        array $data = []
    ) {
        $this->zoneCollectionFactory = $zoneCollectionFactory;
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
            self::ATTRIBUTE_NAME => __('Use Zones Form State'),
        ];

        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * Get attribute element
     *
     * @return AbstractCondition
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
        return 'boolean';
    }

    /**
     * @return string
     */
    public function asHtml()
    {
        $html = $this->getTypeElementHtml() .
            $this->getAttributeElementHtml() .
            $this->getOperatorElementHtml() .
            $this->getRemoveLinkHtml() .
            $this->getChooserContainerHtml();

        return $html;
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
     * Default operator options getter
     * Provides all possible operator options
     *
     * @return array
     */
    public function getDefaultOperatorOptions()
    {
        if (null === $this->_defaultOperatorOptions) {
            $this->_defaultOperatorOptions = [
                '==' => __('')
            ];
        }

        return $this->_defaultOperatorOptions;
    }

    /**
     * @param array $arrAttributes
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function asArray(array $arrAttributes = [])
    {
        $out = [
            'type' => $this->getType(),
            'attribute' => $this->getAttribute(),
            'operator' => $this->getOperator(),
            'value' => true,
            'is_value_processed' => $this->getIsValueParsed(),
        ];

        return $out;
    }

    /**
     * Validate Rule Condition
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     */
    public function validate(AbstractModel $model)
    {
        if (!$model instanceof Quote) {
            $quote = $model->getQuote();
        } else {
            $quote = $model;
        }

        if ($quote instanceof Quote) {
            $model->setData(self::ATTRIBUTE_NAME, false);
            $address = $quote->getShippingAddress();

            $rule = $this->getRule();
            $country = $rule->getZoneCountry();
            $state = $rule->getZoneStateId();

            if ($country) {
                $zoneCollection = $this->getZoneCollectionByState($quote, $country, $state);

                foreach ($zoneCollection as $zone) {
                    if ($zone->validate($address)) {
                        $model->setData(self::ATTRIBUTE_NAME, true);
                        break;
                    }
                }
            }
        }

        return parent::validate($model);
    }

    /**
     * @param Quote $quote
     * @param string $country
     * @param int|null $state
     * @return \Digidirect\ExtendedShippingRates\Model\ResourceModel\Zone\Collection
     */
    public function getZoneCollectionByState($quote, $country, $state = null)
    {
        /** @var \Digidirect\ExtendedShippingRates\Model\ResourceModel\Zone\Collection $zoneCollection */
        $zoneCollection = $this->zoneCollectionFactory->create();
        $zoneCollection
            ->addStoreFilter($quote->getStore()->getId())
            ->addFieldToFilter(ZoneInterface::ATTRIBUTE_SET, 1)
            ->addFieldToFilter(ZoneInterface::IS_ACTIVE, 1);
        if ($country) {
            $zoneCollection->addFieldToFilter(ZoneInterface::COUNTRY_ID, $country);
        }

        if ($state) {
            $zoneCollection->addFieldToFilter(
                ZoneInterface::REGION_ID,
                [
                    'or' => [
                        0 => ['eq' => $state],
                        1 => ['eq' => 0]
                    ]
                ]
            );
        }

        $zoneCollection->setOrder(
            ZoneInterface::PRIORITY,
            \Magento\Framework\Data\Collection\AbstractDb::SORT_ORDER_ASC
        );

        return $zoneCollection;
    }
}
