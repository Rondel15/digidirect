<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\Popups;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Directory\Model\Currency;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\HTTP\PhpEnvironment\ServerAddress;
use Magento\Framework\Module\Manager;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManager;
use Plumrocket\Base\Helper\Base;
use Plumrocket\Newsletterpopup\Helper\Data;
use Plumrocket\Newsletterpopup\Model\Config\Source\Popup\Type as PopupTypeSource;
use Plumrocket\Newsletterpopup\Model\Config\Source\Status;
use Plumrocket\Newsletterpopup\Model\Popup;

class Grid extends Extended
{
    /**
     * @var array
     */
    protected $filtersMap = [
        'entity_id'         => 'main_table.entity_id',
        'name'              => 'main_table.name',
        'views_count'       => 'main_table.views_count',
        'subscribers_count' => 'main_table.subscribers_count',
        'orders_count'      => 'main_table.orders_count',
        'total_revenue'     => 'main_table.total_revenue',
        'start_date'        => 'main_table.start_date',
        'end_date'          => 'main_table.end_date',
        'store_id'          => 'main_table.store_id',
    ];

    /**
     * @var \Plumrocket\Newsletterpopup\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\Popup
     */
    protected $popup;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\Config\Source\Status
     */
    protected $statusSource;

    /**
     * @var \Plumrocket\Base\Helper\Base
     */
    protected $baseHelper;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $moduleList;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $serverAddress;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cacheManager;

    /**
     * @var PopupTypeSource
     */
    private $popupTypeSource;

    /**
     * @param \Magento\Backend\Block\Template\Context                    $context
     * @param \Magento\Backend\Helper\Data                               $backendHelper
     * @param \Plumrocket\Base\Helper\Base                               $baseHelper
     * @param \Plumrocket\Newsletterpopup\Helper\Data                    $dataHelper
     * @param \Plumrocket\Newsletterpopup\Model\Popup                    $popup
     * @param \Plumrocket\Newsletterpopup\Model\Config\Source\Status     $statusSource
     * @param \Magento\Framework\Module\ModuleListInterface              $moduleList
     * @param \Magento\Framework\Module\Manager                          $moduleManager
     * @param \Magento\Store\Model\StoreManager                          $storeManager
     * @param \Magento\Framework\App\ProductMetadataInterface            $productMetadata
     * @param \Magento\Framework\HTTP\PhpEnvironment\ServerAddress       $serverAddress
     * @param \Magento\Framework\App\CacheInterface                      $cacheManager
     * @param \Plumrocket\Newsletterpopup\Model\Config\Source\Popup\Type $popupTypeSource
     * @param array                                                      $data
     */
    public function __construct(
        Context $context,
        BackendHelper $backendHelper,
        Base $baseHelper,
        Data $dataHelper,
        Popup $popup,
        Status $statusSource,
        ModuleListInterface $moduleList,
        Manager $moduleManager,
        StoreManager $storeManager,
        ProductMetadataInterface $productMetadata,
        ServerAddress $serverAddress,
        CacheInterface $cacheManager,
        PopupTypeSource $popupTypeSource,
        array $data = []
    ) {
        $this->baseHelper       = $baseHelper;
        $this->dataHelper       = $dataHelper;
        $this->popup            = $popup;
        $this->statusSource     = $statusSource;
        $this->moduleList       = $moduleList;
        $this->moduleManager    = $moduleManager;
        $this->storeManager     = $storeManager;
        $this->productMetadata  = $productMetadata;
        $this->serverAddress    = $serverAddress;
        $this->cacheManager     = $cacheManager;
        $this->popupTypeSource  = $popupTypeSource;
        parent::__construct($context, $backendHelper, $data);
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('manage_prnewsletterpopup_popups_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->popup
            ->getCollection()
            ->addThemeData();

        foreach ($this->filtersMap as $_field => $_alias) {
            $collection->addFilterToMap($_field, $_alias);
        }

        $this->setCollection($collection);
        $result = parent::_prepareCollection();
        foreach ($collection as $popup) {
            if ($popup->getStoreId() && $popup->getStoreId() != '0') {
                $popup->setStoreId(explode(',', $popup->getStoreId()));
            } else {
                $popup->setStoreId(['0']);
            }
        }
        return $result;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('image', [
            'header'    => __('Thumbnail'),
            'align'     => 'left',
            'index'     => 'image',
            'renderer'  => 'Plumrocket\Newsletterpopup\Block\Adminhtml\Popups\Renderer\Thumbnail',
            'filter'    => false,
            'sortable'  => false,
        ]);

        $this->addColumn('entity_id', [
            'header'    => __('ID'),
            'index'     => 'entity_id',
            'type'      => 'text',
            'width'     => '5%',
        ]);

        $this->addColumn('name', [
            'header'    => __('Name'),
            'index'     => 'name',
            'type'      => 'text',
            'width'     => '20%',
        ]);

        $this->addColumn('type', [
            'header'    => __('Type'),
            'index'     => 'type',
            'type'      => 'options',
            'options'   => $this->popupTypeSource->toOptionHash(),
            'width'     => '20%',
        ]);

        $this->addColumn('views_count', [
            'header'    => __('Views'),
            'index'     => 'views_count',
            'type'      => 'number',
            'width'     => '6%',
            'frame_callback' => [$this, 'decorateInt'],
        ]);

        $this->addColumn('subscribers_count', [
            'header'    => __('Subscriptions'),
            'index'     => 'subscribers_count',
            'type'      => 'number',
            'width'     => '6%',
            'frame_callback' => [$this, 'decorateInt'],
        ]);

        $this->addColumn('conv_rate', [
            'header'    => __('Conversion Rate'),
            'index'     => 'conv_rate',
            'type'      => 'number',
            'width'     => '6%',
            'align'     => 'right',
            'renderer'  => 'Plumrocket\Newsletterpopup\Block\Adminhtml\Popups\Renderer\Rate',
            'filter'    => false,
            'sortable'  => false,
        ]);

        $this->addColumn('orders_count', [
            'header'    => __('Оrders Count'),
            'index'     => 'orders_count',
            'type'      => 'number',
            'width'     => '6%',
            'frame_callback' => [$this, 'decorateInt'],
        ]);

        $this->addColumn('total_revenue', [
            'header'    => __('Total Revenue'),
            'index'     => 'total_revenue',
            'type'      => 'price',
            'currency_code' => (string)$this->dataHelper->getConfig(Currency::XML_PATH_CURRENCY_BASE),
            'width'     => '6%',
        ]);

        $this->addColumn('start_date', [
            'header'    => __('Start Date'),
            'index'     => 'start_date',
            'type'      => 'datetime',
            'width'     => '6%',
            'renderer'  => 'Plumrocket\Newsletterpopup\Block\Adminhtml\Popups\Renderer\Date',
        ]);

        $this->addColumn('end_date', [
            'header'    => __('End Date'),
            'index'     => 'end_date',
            'type'      => 'datetime',
            'width'     => '6%',
            'renderer'  => 'Plumrocket\Newsletterpopup\Block\Adminhtml\Popups\Renderer\Date',
        ]);

        if (!$this->storeManager->isSingleStoreMode()) {
            $this->addColumn('store_id', [
                'header'        => __('Visible In'),
                'index'         => 'store_id',
                'type'          => 'store',
                'store_all'     => true,
                'store_view'    => true,
                'sortable'      => true,
                'width'         => '8%',
                'filter_condition_callback' => [$this, '_filterStoreCondition'],
            ]);
        }

        $this->addColumn('status', [
            'header'    => __('Status'),
            'index'     => 'status',
            'type'      => 'options',
            'options'   => $this->statusSource->toOptionHash(),
            'width'     => '6%',
            'frame_callback' => [$this, 'decorateStatus']
        ]);

        $this->addColumn('action', [
            'header'    => __('Preview'),
            'type'      => 'text',
            'width'     => '3%',
            'renderer'  => 'Plumrocket\Newsletterpopup\Block\Adminhtml\Popups\Renderer\Preview',
            'filter'    => false,
            'sortable'  => false,
            'align'     => 'center',
        ]);

        return parent::_prepareColumns();
    }

    protected function _filterStoreCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $this->getCollection()->addStoreFilter($value);
    }

    /**
     * Decorate status column values
     *
     * @return string
     */
    public function decorateStatus($value, $row, $column, $isExport)
    {
        if ($row->getStatus()) {
            $cell = '<span class="grid-severity-notice"><span>'.$value.'</span></span>';
        } else {
            $cell = '<span class="grid-severity-critical"><span>'.$value.'</span></span>';
        }
        return $cell;
    }

    public function decorateInt($value, $row, $column, $isExport)
    {
        return (string)(int)$value;
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('popup_id');
        $this->getMassactionBlock()
            ->addItem('duplicate', [
                'label'        => __('Duplicate'),
                'url'        => $this->getUrl('*/*/mass', ['action' => 'duplicate'])
            ])
            ->addItem('enable', [
                'label'        => __('Enable'),
                'url'        => $this->getUrl('*/*/mass', ['action' => 'enable'])
            ])
            ->addItem('disable', [
                'label'        => __('Disable'),
                'url'        => $this->getUrl('*/*/mass', ['action' => 'disable'])
            ])
            ->addItem('delete', [
                'label'        => __('Delete'),
                'url'        => $this->getUrl('*/*/mass', ['action' => 'delete']),
                'confirm'    => __('By deleting popup you will also delete history. Are you sure?')
            ]);
        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['id' => $row->getId()]);
    }

    protected function _toHtml()
    {
        return parent::_toHtml();
    }
}
