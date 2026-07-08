<?php

namespace Digidirect\ExtendedCartPriceRules\Model\Rule\Condition;

use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Quote\Model\Quote;
use Digidirect\ExtendedCartPriceRules\Model\ResourceModel\Sales\Order as OrderResource;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class ProductWasBoughtIn
 * @method string getSku()
 * @method string getPeriodType()
 * @package Digidirect\ExtendedCartPriceRules\Model\Rule\Condition
 */
class ProductWasBoughtIn extends AbstractCondition
{
    const ATTRIBUTE_CODE = '__product_was_bought_in';

    const PERIOD_TYPE_OPTION_DAYS = 'day';
    const PERIOD_TYPE_OPTION_WEEKS = 'week';
    const PERIOD_TYPE_OPTION_MONTHS = 'month';
    const PERIOD_TYPE_OPTION_YEAR = 'year';

    /**
     * @var OrderResource
     */
    protected $orderResource;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var array
     */
    protected $lastBoughtProductDate;

    /**
     * OrdersCount constructor.
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param OrderResource $orderResource
     * @param DateTime $dateTime
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        OrderResource $orderResource,
        DateTime $dateTime,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->orderResource = $orderResource;
        $this->dateTime = $dateTime;
    }

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            self::ATTRIBUTE_CODE => __('was bought in period'),
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
     * @return array
     */
    protected function getPeriodTypeSelectOptions()
    {
        return [
            self::PERIOD_TYPE_OPTION_DAYS => __('Day(s)'),
            self::PERIOD_TYPE_OPTION_WEEKS => __('Week(s)'),
            self::PERIOD_TYPE_OPTION_MONTHS => __('Month(s)'),
            self::PERIOD_TYPE_OPTION_YEAR => __('Year(s)'),
        ];
    }

    /**
     * @return string
     */
    protected function getPeriodTypeValueName()
    {
        $value = $this->getPeriodType();
        if ($value === null || '' === $value) {
            return '...';
        }
        $selectOptions = $this->getPeriodTypeSelectOptions();
        return $selectOptions[$value] ?? '...';
    }

    /**
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    protected function getPeriodTypeElement()
    {
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__period_type',
            'select',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][period_type]',
                'values' => $this->getPeriodTypeSelectOptions(),
                'value' => $this->getPeriodType(),
                'value_name' => $this->getPeriodTypeValueName(),
                'data-form-part' => $this->getFormName()
            ]
        )->setRenderer(
            $this->_layout->getBlockSingleton(\Magento\Rule\Block\Editable::class)
        );
    }

    /**
     * @return string
     */
    protected function getPeriodTypeElementHtml()
    {
        return $this->getPeriodTypeElement()->toHtml();
    }

    /**
     * @return string
     */
    protected function getSkuValueName()
    {
        $value = $this->getSku();
        if (!$value) {
            return '...';
        }
        return $value;
    }

    /**
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    protected function getSkuElement()
    {
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__sku',
            'text',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][sku]',
                'value' => $this->getSku(),
                'values' => [],
                'value_name' => $this->getSkuValueName(),
                'after_element_html' => null,
                'explicit_apply' => null,
                'data-form-part' => $this->getFormName()
            ]
        )->setRenderer(
            $this->_layout->getBlockSingleton(\Magento\Rule\Block\Editable::class)
        );
    }

    /**
     * @return string
     */
    protected function getSkuElementHtml()
    {
        $html = $this->getSkuElement()->toHtml();
        return $html;
    }

    /**
     * Get this condition as html.
     *
     * @return string
     */
    public function asHtml()
    {
        return $this->getTypeElementHtml() .
            __('Product is one of %1 and it ', $this->getSkuElementHtml()) .
            $this->getAttributeElementHtml() .
            $this->getOperatorElementHtml() .
            $this->getValueElementHtml() .
            $this->getPeriodTypeElementHtml() .
            $this->getRemoveLinkHtml() .
            $this->getChooserContainerHtml();
    }

    /**
     * Get condition as array.
     *
     * @param array $arrAttributes
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function asArray(array $arrAttributes = [])
    {
        return [
            'type' => $this->getType(),
            'attribute' => $this->getAttribute(),
            'operator' => $this->getOperator(),
            'value' => $this->getValue(),
            'sku' => $this->getSku(),
            'period_type' => $this->getPeriodType(),
            'is_value_processed' => $this->getIsValueParsed(),
        ];
    }

    /**
     * @param array $arr
     * @return AbstractCondition
     */
    public function loadArray($arr)
    {
        $this->setSku($arr['sku'] ?? false);
        $this->setPeriodType($arr['period_type'] ?? false);
        return parent::loadArray($arr);
    }

    /**
     * @return array
     */
    protected function getSkuArray()
    {
        $sku = $this->getSku();
        $sku = trim($sku);
        $result = $sku ? explode(',', $sku) : [];
        $result = array_map('trim', $result);
        $result = array_filter($result);
        $result = array_unique($result);
        sort($result);
        return $result;
    }

    /**
     * @return bool|string
     */
    protected function getOrderCreatedAt()
    {
        $periodLength = (int)$this->getValue();
        if ($periodLength <= 0) {
            return false;
        }
        $periodType = $this->getPeriodType();
        if (!$periodType) {
            return false;
        }

        switch ($periodType) {
            case self::PERIOD_TYPE_OPTION_DAYS:
                return $this->dateTime->gmtDate(null, '-' . $periodLength . ' days');
            case self::PERIOD_TYPE_OPTION_WEEKS:
                return $this->dateTime->gmtDate(null, '-' . $periodLength . ' weeks');
            case self::PERIOD_TYPE_OPTION_MONTHS:
                return $this->dateTime->gmtDate(null, '-' . $periodLength . ' months');
            case self::PERIOD_TYPE_OPTION_YEAR:
                return $this->dateTime->gmtDate(null, '-' . $periodLength . ' year');
            default:
                return false;
        }
    }

    /**
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
        if (!$customerId) {
            return false;
        }

        $skuArray = $this->getSkuArray();
        if (empty($skuArray)) {
            return false;
        }

        $orderCreatedAt = $this->getOrderCreatedAt();
        if (!$orderCreatedAt) {
            return false;
        }

        $lastBoughtProductDate = $this->getLastBoughtProductDate($customerId, $skuArray);
        if (empty($lastBoughtProductDate)) {
            return false;
        }

        $periodLength = $this->getValue();
        $model->setData(self::ATTRIBUTE_CODE, $orderCreatedAt);
        try {
            $this->setValue($lastBoughtProductDate);
            $this->unsValueParsed();
            return parent::validate($model);
        } finally {
            $this->setValue($periodLength);
        }
    }

    /**
     * @param int $customerId
     * @param array $skuArray
     * @return string|false
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getLastBoughtProductDate($customerId, array $skuArray)
    {
        $skuList = implode(',', $skuArray);
        if (!isset($this->lastBoughtProductDate[$customerId][$skuList])) {
            $this->lastBoughtProductDate[$customerId][$skuList] = $this->orderResource
                ->getLastBoughtProductDateByCustomerIdAndSkus($customerId, $skuArray);
        }
        return $this->lastBoughtProductDate[$customerId][$skuList];
    }

    /**
     * Default operator input by type map getter
     *
     * @return array
     */
    public function getDefaultOperatorInputByType()
    {
        if (null === $this->_defaultOperatorInputByType) {
            $this->_defaultOperatorInputByType = [
                'string' => ['==', '!=', '>=', '>', '<=', '<', '{}', '!{}', '()', '!()'],
                'numeric' => ['<=', '<'], //removed some default options
                'date' => ['==', '>=', '<='],
                'select' => ['==', '!=', '<=>'],
                'boolean' => ['==', '!=', '<=>'],
                'multiselect' => ['{}', '!{}', '()', '!()'],
                'grid' => ['()', '!()'],
            ];
            $this->_arrayInputTypes = ['multiselect', 'grid'];
        }
        return $this->_defaultOperatorInputByType;
    }
}
