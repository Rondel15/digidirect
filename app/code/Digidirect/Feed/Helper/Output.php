<?php

namespace Digidirect\Feed\Helper;

use Digidirect\Feed\Export\Filter\Pool as FilterPool;
use Digidirect\Feed\Export\Resolver\Pool as ResolverPool;
use Digidirect\Feed\Model\Feed;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Eav\Model\Config as EavConfig;

class Output extends AbstractHelper
{
    /**
     * @var FilterPool
     */
    protected $filterPool;

    /**
     * @var ResolverPool
     */
    protected $resolverPool;

    /**
     * @var EavConfig
     */
    protected $eavConfig;

    /**
     * @var array
     */
    protected $operatorInputByType = [
        'string' => ['==', '!=', '>=', '>', '<=', '<', '{}', '!{}'],
        'numeric' => ['==', '!=', '>=', '>', '<=', '<'],
        'date' => ['==', '>=', '<='],
        'select' => ['==', '!='],
        'boolean' => ['==', '!='],
        'multiselect' => ['{}', '!{}', '()', '!()'],
        'grid' => ['()', '!()'],
    ];

    /**
     * @var array
     */
    protected $operatorOptions = [
        '==' => 'is',
        '!=' => 'is not',
        '>=' => 'equals or greater than',
        '<=' => 'equals or less than',
        '>' => 'greater than',
        '<' => 'less than',
        '{}' => 'contains',
        '!{}' => 'does not contain',
        '()' => 'is one of',
        '!()' => 'is not one of',
    ];

    /**
     * Output constructor.
     * @param Context $context
     * @param FilterPool $filterPool
     * @param ResolverPool $resolverPool
     * @param EavConfig $eavConfig
     */
    public function __construct(
        Context $context,
        FilterPool $filterPool,
        ResolverPool $resolverPool,
        EavConfig $eavConfig
    ) {
        $this->filterPool = $filterPool;
        $this->resolverPool = $resolverPool;
        $this->eavConfig = $eavConfig;

        parent::__construct($context);
    }

    /**
     * List of defined attributes to export
     *
     * @return array
     */
    public function getAttributeOptions()
    {
        $options = [];

        foreach ($this->resolverPool->getResolvers() as $resolver) {
            $attributes = $resolver->getAttributes();

            asort($attributes);

            foreach ($attributes as $code => $label) {
                $group = $this->getAttributeGroup($code);
                $options[$group]['label'] = $group;
                $options[$group]['value'][] = ['value' => $code, 'label' => $label];
            }
        }

        usort($options, function ($a, $b) {
            return strcmp($a['label'], $b['label']);
        });

        return array_values($options);
    }

    /**
     * List of pattern types
     *
     * @return array
     */
    public function getPatternTypeOptions()
    {
        return [
            [
                'label' => 'Pattern',
                'value' => 'pattern'
            ],
            [
                'label' => 'Attribute',
                'value' => ''
            ],
            [
                'label' => 'Parent Product',
                'value' => 'parent'
            ],
            [
                'label' => 'Only Parent Product',
                'value' => 'only_parent',
            ],
            [
                'label' => 'Grouped Product',
                'value' => 'grouped',
            ],
        ];
    }

    /**
     * List of filters
     *
     * @return array
     */
    public function getFilterOptions()
    {
        return $this->filterPool->getFilters();
    }

    /**
     * Attribute group
     *
     * @param string $code
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getAttributeGroup($code)
    {
        $primary = [
            'attribute_set',
            'attribute_set_id',
            'entity_id',
            'full_description',
            'meta_description',
            'meta_keyword',
            'meta_title',
            'name',
            'short_description',
            'description',
            'sku',
            'status',
            'status_parent',
            'url',
            'url_key',
            'visibility',
            'type_id'
        ];

        $stock = [
            'is_in_stock',
            'qty',
            'manage_stock',
        ];

        $price = [
            'tax_class_id',
            'special_from_date',
            'special_to_date',
            'cost',
            'msrp',
        ];

        if (in_array($code, $primary)) {
            $group = __('1. Primary Attributes');
        } elseif (in_array($code, $stock)) {
            $group = __('5. Stock Attributes');
        } elseif (in_array($code, $price) || strpos($code, 'price') !== false) {
            $group = __('2. Prices & Taxes');
        } elseif (strpos($code, 'image') !== false || strpos($code, 'thumbnail') !== false) {
            $group = __('4. Images');
        } elseif (strpos($code, 'category') !== false) {
            $group = __('3. Category');
        } elseif (strpos($code, 'dynamic:') !== false) {
            $group = __('6. Dynamic Attributes');
        } elseif (strpos($code, 'mapping:') !== false) {
            $group = __('7. Category Mapping');
        } else {
            $group = __('8. Others Attributes');
        }

        return $group->__toString();
    }

    /**
     * @param string $attributeCode
     * @return array
     */
    public function getAttributeOperators($attributeCode)
    {
        $conditions = [];

        $attribute = $this->getAttribute($attributeCode);

        $type = 'string';

        if ($attribute) {
            switch ($attribute->getFrontendInput()) {
                case 'select':
                    $type = 'select';
                    break;

                case 'multiselect':
                    $type = 'multiselect';
                    break;

                case 'date':
                    $type = 'date';
                    break;

                case 'boolean':
                    $type = 'boolean';
                    break;

                default:
                    $type = 'string';
            }
        }
        foreach ($this->operatorInputByType[$type] as $operator) {
            $operatorTitle = __($this->operatorOptions[$operator]);
            $conditions[] = [
                'label' => $operatorTitle,
                'value' => $operator,
            ];
        }

        return $conditions;
    }

    /**
     * @param string $attributeCode
     * @return array
     */
    public function getAttributeValues($attributeCode)
    {
        $result = [];

        $attribute = $this->getAttribute($attributeCode);
        if ($attribute) {
            if ($attribute->getFrontendInput() == 'select' || $attribute->getFrontendInput() == 'multiselect') {
                $result[] = ['label' => __('not set'), 'value' => ''];
                foreach ($attribute->getSource()->getAllOptions() as $option) {
                    $result[] = [
                        'label' => $option['label'],
                        'value' => $option['value'],
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * @param string $code
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute|false
     */
    protected function getAttribute($code)
    {
        return $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $code);
    }
}
