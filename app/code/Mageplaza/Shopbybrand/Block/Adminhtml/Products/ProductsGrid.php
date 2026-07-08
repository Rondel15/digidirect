<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Shopbybrand\Block\Adminhtml\Products;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer\Price;
use Mageplaza\Shopbybrand\Block\Adminhtml\Products\Edit\Tab\Renderer\Type;
use Mageplaza\Shopbybrand\Helper\Data as BrandHelper;

/**
 * Class ProductsGrid
 * @package Mageplaza\Shopbybrand\Block\Adminhtml\Products
 */
class ProductsGrid extends Extended
{
    /**
     * @var ProductFactory
     */
    protected $_productFactory;
    /**
     * @var BrandHelper
     */
    protected $helper;
    /**
     * @var Visibility
     */
    protected $_productVisibility;

    /**
     * @var string
     */
    protected $attributeCode;
    /**
     * @var int
     */
    protected $storeId;
    /**
     * @var int
     */
    protected $optionId;

    /**
     * @param Context $context
     * @param Data $backendHelper
     * @param ProductFactory $productFactory
     * @param Visibility $productVisibility
     * @param BrandHelper $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        ProductFactory $productFactory,
        Visibility $productVisibility,
        BrandHelper $helper,
        array $data = []
    ) {
        $this->_productFactory    = $productFactory;
        $this->_productVisibility = $productVisibility;
        $this->helper             = $helper;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('product_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->attributeCode = $this->helper->getAttributeCode($this->storeId);
        $this->storeId       = $this->getRequest()->getParam('store_id');
        $this->optionId      = $this->getRequest()->getParam('option_id');
    }

    /**
     * @return ProductsGrid
     */
    protected function _prepareCollection()
    {
        $collection    = $this->_productFactory->create()->getCollection();
        $attributeCode = $this->attributeCode;
        $collection->setVisibility($this->_productVisibility->getVisibleInSiteIds());
        $collection->addAttributeToSelect(['name', 'sku', 'price', $attributeCode]);
        $collection->joinField(
            'barcode_qty',
            'cataloginventory_stock_item',
            'qty',
            'product_id=entity_id',
            '{{table}}.stock_id=1 AND {{table}}.website_id=0',
            'left'
        );
        $collection->addFieldToFilter('type_id', [
            'nin' => [
                'bundle',
                'grouped'
            ]
        ]);
        $collection->joinTable('catalog_product_relation', 'child_id=entity_id', [
            'parent_id' => 'parent_id'
        ], null, 'left')
            ->addAttributeToFilter([
                [
                    'attribute' => 'parent_id',
                    'null'      => null
                ]
            ]);
        $collection->addFieldToFilter($attributeCode, [
            ['attribute' => $attributeCode, 'null' => true],
            ['attribute' => $attributeCode, 'eq' => $this->optionId]
        ]);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Sets sorting order by some column
     *
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     *
     * @return $this
     */
    protected function _setCollectionOrder($column)
    {
        $collection = $this->getCollection();
        if ($collection) {
            $columnIndex = $column->getFilterIndex() ? $column->getFilterIndex() : $column->getIndex();
            if ($columnIndex === 'price') {
                // custom Sort with Price.
                $collection->setOrder('price');
                $collection->getSelect()->reset(\Zend_Db_Select::ORDER);
                $collection->getSelect()->order(new \Zend_Db_Expr('price_index.price ' . $column->getDir()));
            } else {
                $collection->setOrder($columnIndex, strtoupper($column->getDir()));
            }

        }

        return $this;
    }

    /**
     * @return Extended
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', [
            'header'           => __('Product ID'),
            'type'             => 'number',
            'index'            => 'entity_id',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id',
            'sortable'         => true
        ]);
        $this->addColumn('name', [
            'header'   => __('Name'),
            'index'    => 'name',
            'type'     => 'text',
            'sortable' => true
        ]);
        $this->addColumn('sku', [
            'header'   => __('SKU'),
            'index'    => 'sku',
            'type'     => 'text',
            'sortable' => true
        ]);
        $this->addColumn('barcode_qty', [
            'header'   => __('Quantity'),
            'type'     => 'number',
            'index'    => 'barcode_qty',
            'sortable' => true,
        ]);
        $this->addColumn('price', [
            'header'           => __('Price'),
            'column_css_class' => 'price',
            'type'             => 'currency',
            'currency_code'    => $this->_storeManager->getStore()->getBaseCurrencyCode(),
            'index'            => 'price',
            'sortable'         => true,
            'renderer'         => Price::class
        ]);
        $this->addColumn('type_id', [
            'header'   => __('Type'),
            'type'     => 'text',
            'index'    => 'type_id',
            'sortable' => true,
            'renderer' => Type::class
        ]);

        return parent::_prepareColumns();
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        if (!$this->_request->getPostValue('internal_entity_id')) {
            $brandProIds = $this->getProductIdsOfBrand();
            $this->_request->setPostValue('internal_entity_id', $brandProIds);
        }
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('entity_id');
        $this->getMassactionBlock()->setUseAjax(true);
        $this->getMassactionBlock()
            ->addItem('add_product', [
                'label'    => __('Add Products'),
                'url'      => $this->getUrl('mpbrand/*/massAddProducts'),
                'complete' => 'MpOnCompleteMassActionProduct();'
            ])->addItem('un_set_brand', [
                'label'    => __('Remove Products'),
                'url'      => $this->getUrl('mpbrand/*/massUnSetBrand'),
                'complete' => 'MpOnCompleteMassActionProduct();'
            ]);

        return $this;
    }

    /**
     * @return string
     */
    public function getProductIdsOfBrand()
    {
        $collection = $this->_productFactory->create()->getCollection();
        $collection->addAttributeToSelect([$this->attributeCode]);
        $collection->addFieldToFilter($this->attributeCode, $this->optionId);

        return implode(',', $collection->getAllIds());
    }
}
