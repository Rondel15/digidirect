<?php

namespace Digidirect\ExtendedCartPriceRulesMSI\Model\Rule\Condition;

use Magento\Framework\Exception\LocalizedException;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Quote\Model\Quote;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Psr\Log\LoggerInterface;

/**
 * Class ProductWasBoughtIn
 * @method string getSourceType()
 * @method string getQuantity()
 * @package Digidirect\ExtendedCartPriceRules\Model\Rule\Condition
 */
class ProductQuantityInSource extends AbstractCondition
{
    const ATTRIBUTE_CODE = '__product_quantity_in_source';

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;
    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ProductQuantityInSource constructor.
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param SourceRepositoryInterface $sourceRepository
     * @param SourceItemRepositoryInterface $sourceItemRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        SourceRepositoryInterface $sourceRepository,
        SourceItemRepositoryInterface $sourceItemRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        LoggerInterface $logger,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->sourceRepository = $sourceRepository;
        $this->sourceItemRepository = $sourceItemRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
    }

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            self::ATTRIBUTE_CODE => __('the quantity of the products that are added to the cart will be'),
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
     * @return array
     */
    public function getSourceTypeSelectOptions()
    {
        $opt = [];
        $sourceList = $this->sourceRepository->getList();
        foreach ($sourceList->getItems() as $key => $value) {
            $opt[] = ['value' => $key, 'label' => $value->getName()];
        }
        return $opt;
    }

    /**
     * @return string
     */
    public function getSourceTypeName()
    {
        $options = $this->getSourceTypeSelectOptions();
        foreach ($options as $option) {
            if ($option['value'] == $this->getSourceType()) {
                return $option['label'];
            }
        }
        return '...';
    }

    /**
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    protected function getSourceTypeElement()
    {
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__source_type',
            'select',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][source_type]',
                'values' => $this->getSourceTypeSelectOptions(),
                'value' => $this->getSourceType(),
                'value_name' => $this->getSourceTypeName(),
                'data-form-part' => $this->getFormName()
            ]
        )->setRenderer(
            $this->_layout->getBlockSingleton(\Magento\Rule\Block\Editable::class)
        );
    }

    /**
     * @return string
     */
    protected function getSourceTypeElementHtml()
    {
        return $this->getSourceTypeElement()->toHtml();
    }

    /**
     * @return string
     */
    protected function getQuantityValueName()
    {
        $value = $this->getQuantity();
        if (!$value) {
            return '...';
        }
        return $value;
    }

    /**
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    protected function getQuantityElement()
    {
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__quantity',
            'text',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][quantity]',
                'value' => $this->getQuantity(),
                'values' => [],
                'value_name' => $this->getQuantityValueName(),
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
    protected function getQuantityElementHtml()
    {
        $html = $this->getQuantityElement()->toHtml();
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
            __('in the source %1 and it ', $this->getSourceTypeElementHtml()) .
            $this->getAttributeElementHtml() .
            $this->getOperatorElementHtml() .
            $this->getQuantityElementHtml() .
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
            'value' => $this->getValue(), //ned delete!!!!!!
            'source_type' => $this->getSourceType(),
            'quantity' => $this->getQuantity(),
            'is_value_processed' => $this->getIsValueParsed(),
        ];
    }

    /**
     * @param array $arr
     * @return AbstractCondition
     */
    public function loadArray($arr)
    {
        $this->setSourceType($arr['source_type'] ?? false);
        $this->setQuantity($arr['quantity'] ?? false);
        return parent::loadArray($arr);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $resultsValidation = [];
        $quote = $model;
        if (!$quote instanceof Quote) {
            $quote = $model->getQuote();
        }

        $sourceCode = $this->getSourceType();
        if (empty($sourceCode)) {
            return false;
        }

        $quantity = $this->getQuantity();
        if (empty($quantity) || !is_numeric($quantity) || strpos($quantity, '.') !== false) {
            return false;
        }

        $quoteItems = $quote->getAllVisibleItems();
        if (empty($quoteItems)) {
            return false;
        }

        try {
            $quoteItemsQuantityInSource = $this->getQuoteItemsQuantityBySource($quoteItems, $sourceCode);
            $this->setValue($quantity);
            foreach ($quoteItemsQuantityInSource as $itemQuantityInSource) {
                if ($itemQuantityInSource < 0) {
                    return false;
                }
                $model->setData(self::ATTRIBUTE_CODE, $itemQuantityInSource);
                $validatedResult = parent::validate($model);
                if ($validatedResult === false) {
                    return $validatedResult;
                }
                $resultsValidation[] = $validatedResult;
            }
            return $this->checkAllProductResultValidation($resultsValidation);
        } catch (LocalizedException $e) {
            $this->logger->critical($e->getMessage());
            return false;
        }
    }

    /**
     * @param array $quoteItems
     * @param string $sourceCode
     * @return array
     */
    public function getQuoteItemsQuantityBySource($quoteItems, $sourceCode)
    {
        $skuAndQtyItems = array_reduce($quoteItems, function ($acc, $value) {
            $acc[$value->getSku()] = $value->getQty();
            return $acc;
        }, []);
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SKU, array_keys($skuAndQtyItems), 'in')
            ->addFilter(SourceItemInterface::SOURCE_CODE, $sourceCode)
            ->create();

        $sourceItems = $this->sourceItemRepository->getList($searchCriteria)->getItems();

        return array_reduce($sourceItems, function ($acc, $value) use ($skuAndQtyItems) {
            if ($value && $value instanceof SourceItemInterface) {
                $quantityInSource = $value->getQuantity();
                $quantityInQuote = $skuAndQtyItems[$value->getSku()];
                $acc[] = $quantityInSource - $quantityInQuote;
            }
            return $acc;
        }, []);
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

    /**
     * @param array $resultsValidation
     * @return bool
     */
    public function checkAllProductResultValidation($resultsValidation)
    {
        if (!empty($resultsValidation)) {
            $filteredResult = array_filter($resultsValidation);
            $diffResult = array_diff($resultsValidation, $filteredResult);
            return empty($diffResult);
        }
        return false;
    }
}
