<?php

namespace Digidirect\Feed\Block\Adminhtml\Report;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Grid\Extended as ExtendedGrid;
use Magento\Backend\Helper\Data as BackendHelper;
use Digidirect\Feed\Model\ResourceModel\Feed\CollectionFactory as FeedCollectionFactory;

class Grid extends ExtendedGrid
{
    /**
     * @var FeedCollectionFactory
     */
    protected $feedCollectionFactory;

    /**
     * Grid constructor.
     * @param Context $context
     * @param BackendHelper $backendHelper
     * @param FeedCollectionFactory $feedCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        BackendHelper $backendHelper,
        FeedCollectionFactory $feedCollectionFactory,
        array $data = []
    ) {
        $this->feedCollectionFactory = $feedCollectionFactory;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('feed_report_grid');
        $this->setDefaultSort('name');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareCollection()
    {
        /**
         * @var $collection \Digidirect\Feed\Model\ResourceModel\Feed\Collection
         */

        $collection = $this->feedCollectionFactory->create();
        $connection = $collection->getConnection();
        $select = $collection->getSelect();
        $select
            ->reset(\Zend_Db_Select::COLUMNS)
            ->group('main_table.feed_id')
            ->joinLeft(
                ['report' => $collection->getTable('digidirect_feed_report')],
                'main_table.feed_id = report.feed_id',
                []
            )
            ->columns([
                'feed_id' => 'main_table.feed_id',
                'name' => 'main_table.name',
                'is_active' => 'main_table.is_active',
                'report_enabled' => 'main_table.report_enabled',
                'clicks_count' => $connection->getIfNullSql('SUM(report.is_click)'),
                'orders_count' => $connection->getIfNullSql('SUM(report.is_order)'),
                'orders_subtotal_sum' => $connection->getIfNullSql('SUM(report.subtotal)'),
            ]);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn('feed_id', [
            'header' => __('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'feed_id',
            'filter_index' => 'main_table.feed_id',
            'type' => 'number',
        ]);

        $this->addColumn('name', [
            'header' => __('Name'),
            'align' => 'right',
            'index' => 'name',
            'filter_index' => 'main_table.name',
        ]);

        $this->addColumn('is_active', [
            'header' => __('Is Active'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'is_active',
            'filter_index' => 'main_table.is_active',
            'type' => 'options',
            'options' => [
                1 => __('Enabled'),
                0 => __('Disabled'),
            ],
        ]);

        $this->addColumn('report_enabled', [
            'header' => __('Report Enabled'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'report_enabled',
            'filter_index' => 'main_table.report_enabled',
            'type' => 'options',
            'options' => [
                1 => __('Enabled'),
                0 => __('Disabled'),
            ],
        ]);

        $this->addColumn('created_at', [
            'header' => __('Dates Filter'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'created_at',
            'filter_index' => 'report.created_at',
            'type' => 'datetime',
        ]);

        $this->addColumn('clicks_count', [
            'header' => __('Clicks Count'),
            'align' => 'right',
            'width' => '80px',
            'index' => 'clicks_count',
            'type' => 'number',
            'filter' => false,
            'sortable' => false,
        ]);

        $this->addColumn('orders_count', [
            'header' => __('Orders Count'),
            'align' => 'right',
            'width' => '80px',
            'index' => 'orders_count',
            'type' => 'number',
            'filter' => false,
            'sortable' => false,
        ]);

        $this->addColumn('orders_subtotal_sum', [
            'header' => __('Orders subtotal sum'),
            'align' => 'right',
            'width' => '80px',
            'index' => 'orders_subtotal_sum',
            'type' => 'number',
            'filter' => false,
            'sortable' => false,
        ]);

        return parent::_prepareColumns();
    }

    /**
     * {@inheritdoc}
     */
    public function getRowUrl($row)
    {
        return false;
    }
}
