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

namespace Mageplaza\Shopbybrand\Block\Adminhtml\Report;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Framework\Exception\FileSystemException;
use Mageplaza\Shopbybrand\Model\ResourceModel\Grid\Report\CollectionFactory;
use Magento\Framework\Registry;

/**
 * Class ReportGrid
 * @package Mageplaza\Shopbybrand\Block\Adminhtml\Report
 */
class ReportGrid extends Extended
{

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param Context $context
     * @param Data $backendHelper
     * @param Registry $registry
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        Registry $registry,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->_coreRegistry     = $registry;
        $this->collectionFactory = $collectionFactory;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @throws FileSystemException
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('mpbrand-grid');
        $this->setDefaultSort('attribute_name');
        $this->setDefaultDir('ASC');
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setUseAjax(true);
    }

    /**
     * @return Grid
     */
    protected function _prepareCollection()
    {
        $dateRange  = $this->_coreRegistry->registry('mpShopbybrand_date');
        $fromDate   = $dateRange[0];
        $toDate     = $dateRange[1];
        $collection = $this->collectionFactory->create();

        if ($fromDate !== null) {
            $collection->addFieldToFilter('order_created_at', ['gteq' => $fromDate]);
        }
        if ($toDate !== null) {
            $collection->addFieldToFilter('order_created_at', ['lteq' => $toDate]);
        }

        $collection->addFieldToFilter('attribute_name', ['notnull' => true]);
        $collection->addFieldToFilter(['row_total', 'refunded'], [['gt' => 0], ['gt' => 0]]);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('attribute_name', [
            'header'   => __('Brand'),
            'index'    => 'attribute_name',
            'sortable' => false
        ]);

        $this->addColumn('qty_item', [
            'header'   => __('Ordered Item Quantity'),
            'index'    => 'qty_item',
            'type'     => 'text',
            'sortable' => false
        ]);

        $this->addColumn('qty_ordered', [
            'header'   => __('Order Count'),
            'index'    => 'qty_ordered',
            'type'     => 'text',
            'sortable' => false
        ]);

        $this->addColumn('total', [
            'header'   => __('Total Revenue'),
            'index'    => 'total',
            'type'     => 'currency',
            'sortable' => false
        ]);

        $this->addColumn('refunded', [
            'header'   => __('Refunded'),
            'index'    => 'refunded',
            'type'     => 'currency',
            'sortable' => false
        ]);

        $this->addColumn('discount', [
            'header'   => __('Discount'),
            'index'    => 'discount',
            'type'     => 'currency',
            'sortable' => false
        ]);

        $this->addColumn('tax', [
            'header'   => __('Tax'),
            'index'    => 'tax',
            'type'     => 'currency',
            'sortable' => false
        ]);

        return parent::_prepareColumns();
    }

    /**
     * {@inheritdoc}
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/reportgrid', ['_current' => true]);
    }
}
