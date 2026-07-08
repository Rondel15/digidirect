<?php

namespace Digidirect\ExtendedCartPriceRules\Model\Rule\Condition;

use Magento\Framework\Exception\LocalizedException;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

/**
 * Class ProductSumVolume
 * @method string getWidth()
 * @method string getLength()
 * @method string getHeight()
 * @method string setWidth($data)
 * @method string setLength($data)
 * @method string setHeight($data)
 * @package Digidirect\ExtendedCartPriceRules\Model\Rule\Condition
 */
class ProductSumVolume extends AbstractCondition
{
    const ATTRIBUTE_CODE = '__product_sum_volume';

    const WIDTH_PRODUCT = 'width_product';
    const LENGTH_PRODUCT = 'length_product';
    const HEIGHT_PRODUCT = 'height_product';
    const QUANTITY_PRODUCT = 'quantity_product';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ProductSumVolume constructor.
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        LoggerInterface $logger,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->logger = $logger;
    }

    /**
     * @return float|int
     */
    public function getParsedWidth()
    {
        return $this->toNumber($this->getWidth());
    }

    /**
     * @return float|int
     */
    public function getParsedLength()
    {
        return $this->toNumber($this->getLength());
    }

    /**
     * @return float|int
     */
    public function getParsedHeight()
    {
        return $this->toNumber($this->getHeight());
    }
    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            self::ATTRIBUTE_CODE => __('The sum volume of the products that are added to the cart '),
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
        return 'string';
    }

    /**
     * Get value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        return 'string';
    }

    /**
     * @return string
     */
    public function getLengthValueName()
    {
        $value = $this->getLength();
        if (!$value) {
            return '...';
        }
        return $value;
    }

    /**
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function getLengthElement()
    {
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__length',
            'text',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][length]',
                'value' => $this->getLength(),
                'values' => [],
                'value_name' => $this->getLengthValueName(),
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
    public function getLengthElementHtml()
    {
        $html = $this->getLengthElement()->toHtml();
        return $html;
    }

    /**
     * @return string
     */
    public function getWidthValueName()
    {
        $value = $this->getWidth();
        if (!$value) {
            return '...';
        }
        return $value;
    }

    /**
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function getWidthElement()
    {
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__width',
            'text',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][width]',
                'value' => $this->getWidth(),
                'values' => [],
                'value_name' => $this->getWidthValueName(),
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
    public function getWidthElementHtml()
    {
        $html = $this->getWidthElement()->toHtml();
        return $html;
    }

    /**
     * @return string
     */
    public function getHeightValueName()
    {
        $value = $this->getHeight();
        if (!$value) {
            return '...';
        }
        return $value;
    }

    /**
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function getHeightElement()
    {
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__height',
            'text',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][height]',
                'value' => $this->getHeight(),
                'values' => [],
                'value_name' => $this->getHeightValueName(),
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
    public function getHeightElementHtml()
    {
        $html = $this->getHeightElement()->toHtml();
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
            $this->getAttributeElementHtml() .
            $this->getOperatorElementHtml() .
            __('the production of multiplication of Length %1 ', $this->getLengthElementHtml()) .
            __('Width %1 ', $this->getWidthElementHtml()) .
            __('Height %1 ', $this->getHeightElementHtml()) .
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
            'length' => $this->getLength(),
            'width' => $this->getWidth(),
            'height' => $this->getHeight(),
            'is_value_processed' => $this->getIsValueParsed(),
        ];
    }

    /**
     * @param array $arr
     * @return AbstractCondition
     */
    public function loadArray($arr)
    {
        $this->setLength($arr['length'] ?? false);
        $this->setWidth($arr['width'] ?? false);
        $this->setHeight($arr['height'] ?? false);
        return parent::loadArray($arr);
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

        $length = $this->getParsedLength();
        if (empty($length) || !is_numeric($length)) {
            return false;
        }

        $width = $this->getParsedWidth();
        if (empty($width) || !is_numeric($width)) {
            return false;
        }
        $height = $this->getParsedHeight();
        if (empty($height) || !is_numeric($height)) {
            return false;
        }

        $quoteItems = $quote->getAllVisibleItems();
        if (empty($quoteItems)) {
            return false;
        }

        $currentVolume = $this->calculateVolume($length, $width, $height);
        try {
            $this->setValue($currentVolume);
            $totalBasketVolume = $this->calculateTotalVolume($quoteItems);
            if (empty($totalBasketVolume)) {
                return false;
            }
            $model->setData(self::ATTRIBUTE_CODE, $totalBasketVolume);
            return parent::validate($model);
        } catch (LocalizedException $e) {
            $this->logger->critical($e->getMessage());
            return false;
        }
    }

    /**
     * @param array $quoteItem
     * @return int
     */
    public function calculateTotalVolume($quoteItem)
    {
        $totalVolume = 0;
        foreach ($quoteItem as $item) {
            $itemData = $this->getItemData($item);
            $itemVolume = $this->calculateVolume(
                $itemData[self::LENGTH_PRODUCT],
                $itemData[self::WIDTH_PRODUCT],
                $itemData[self::HEIGHT_PRODUCT]
            );
            if (empty($itemVolume)) {
                return 0;
            }
            $totalVolume += $itemVolume * $itemData[self::QUANTITY_PRODUCT];
        }
        return $totalVolume;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return array
     */
    public function getItemData($item)
    {
        $itemProduct = $item->getProduct();
        return [
            self::LENGTH_PRODUCT   => !empty($itemProduct) ? $itemProduct->getTsDimensionsLength() : 0,
            self::WIDTH_PRODUCT    => !empty($itemProduct) ? $itemProduct->getTsDimensionsWidth() : 0,
            self::HEIGHT_PRODUCT   => !empty($itemProduct) ? $itemProduct->getTsDimensionsHeight() : 0,
            self::QUANTITY_PRODUCT => !empty($item) ? $item->getQty() : 0,
        ];
    }

    /**
     * @param string $length
     * @param string $width
     * @param string $height
     * @return int
     */
    public function calculateVolume($length, $width, $height)
    {
        if (!empty($length) && !empty($width) && !empty($height)) {
            return floatval($length) * floatval($width) * floatval($height);
        }
        return 0;
    }

    /**
     * @param string $num
     * @return float|int
     */
    public function toNumber($num)
    {
        if ($num) {
            $dotPos = strrpos($num, '.');
            $commaPos = strrpos($num, ',');
            $sep = (($dotPos > $commaPos) && $dotPos) ? $dotPos :
                ((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);

            if (!$sep) {
                return floatval(preg_replace("/[^0-9]/", "", $num));
            }

            return floatval(
                preg_replace("/[^0-9]/", "", substr($num, 0, $sep)) . '.' .
                preg_replace("/[^0-9]/", "", substr($num, $sep + 1, strlen($num)))
            );
        }
        return 0;
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
                'string' => ['>=', '>', '<=', '==', '<'],
                'numeric' => ['==', '!=', '>=', '>', '<=', '<', '()', '!()'],
                'date' => ['==', '>=', '<='],
                'select' => ['==', '!=', '<=>'],
                'boolean' => ['==', '!=', '<=>'],
                'multiselect' => ['{}', '!{}', '()', '!()'],
                'grid' => ['()', '!()'],
            ];
        }
        return $this->_defaultOperatorInputByType;
    }
}
