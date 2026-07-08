<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\Templates;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\HTTP\PhpEnvironment\ServerAddress;
use Magento\Framework\Module\Manager;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManager;
use Plumrocket\Newsletterpopup\Helper\Adminhtml;
use \Plumrocket\Base\Helper\Base;

class Grid extends Extended
{
    /**
     * @var array
     */
    protected $filtersMap = [
        'entity_id'         => 'main_table.entity_id',
        'name'              => 'main_table.name',
        'updated_at'        => 'main_table.updated_at',
        'created_at'        => 'main_table.created_at',
        'base_template_id'  => 'main_table.base_template_id',
    ];

    /**
     * @var Adminhtml
     */
    protected $adminhtmlHelper;

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
     * Grid constructor.
     *
     * @param Context                  $context
     * @param BackendHelper            $backendHelper
     * @param Adminhtml                $adminhtmlHelper
     * @param Base                     $baseHelper
     * @param ModuleListInterface      $moduleList
     * @param Manager                  $moduleManager
     * @param StoreManager             $storeManager
     * @param ProductMetadataInterface $productMetadata
     * @param ServerAddress            $serverAddress
     * @param CacheInterface           $cacheManager
     * @param array                    $data
     */
    public function __construct(
        Context $context,
        BackendHelper $backendHelper,
        Adminhtml $adminhtmlHelper,
        Base $baseHelper,
        ModuleListInterface $moduleList,
        Manager $moduleManager,
        StoreManager $storeManager,
        ProductMetadataInterface $productMetadata,
        ServerAddress $serverAddress,
        CacheInterface $cacheManager,
        array $data = []
    ) {
        $this->adminhtmlHelper  = $adminhtmlHelper;
        $this->baseHelper       = $baseHelper;
        $this->moduleList       = $moduleList;
        $this->moduleManager    = $moduleManager;
        $this->storeManager     = $storeManager;
        $this->productMetadata  = $productMetadata;
        $this->serverAddress    = $serverAddress;
        $this->cacheManager     = $cacheManager;
        parent::__construct($context, $backendHelper, $data);
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('manage_prnewsletterpopup_templates_grid');
        $this->setDefaultSort('updated_at');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->adminhtmlHelper->getTemplates();

        foreach ($this->filtersMap as $_field => $_alias) {
            $collection->addFilterToMap($_field, $_alias);
        }

        $this->setCollection($collection);
        $result = parent::_prepareCollection();

        foreach ($collection as $template) {
            if ($template->getStoreId() && $template->getStoreId() != '0') {
                $template->setStoreId(explode(',', $template->getStoreId()));
            } else {
                $template->setStoreId(['0']);
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
            'renderer'  => 'Plumrocket\Newsletterpopup\Block\Adminhtml\Templates\Renderer\Thumbnail',
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
            'header'    => __('Theme Name'),
            'index'     => 'name',
            'type'      => 'text',
            'width'     => '50%',
        ]);

        $this->addColumn('updated_at', [
            'header'    => __('Updated At'),
            'index'     => 'updated_at',
            'type'      => 'datetime',
            'width'     => '6%',
            'renderer'  => 'Plumrocket\Newsletterpopup\Block\Adminhtml\Templates\Renderer\Date',
        ]);

        $this->addColumn('created_at', [
            'header'    => __('Created At'),
            'index'     => 'created_at',
            'type'      => 'datetime',
            'width'     => '6%',
            'renderer'  => 'Plumrocket\Newsletterpopup\Block\Adminhtml\Templates\Renderer\Date',
        ]);

        $this->addColumn('template_type', [
            'header'    => __('Type'),
            'index'     => 'template_type',
            'type'      => 'options',
            'options'   => [
                            '-1'    => 'Default Theme',
                            '1'     => 'My Theme',
                        ],
            'width'     => '6%',
        ]);

        $this->addColumn('preview', [
            'header'    => __('Preview'),
            'type'      => 'text',
            'width'     => '3%',
            'renderer'  => 'Plumrocket\Newsletterpopup\Block\Adminhtml\Templates\Renderer\Preview',
            'filter'    => false,
            'sortable'  => false,
            'align'     => 'center',
        ]);

        $this->addColumn('action', [
            'header'    => __('Action'),
            'type'      => 'text',
            'width'     => '3%',
            'renderer'  => 'Plumrocket\Newsletterpopup\Block\Adminhtml\Templates\Renderer\Action',
            'filter'    => false,
            'sortable'  => false,
            'align'     => 'center',
        ]);

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('template_id');
        $this->getMassactionBlock()
            ->addItem('duplicate', [
                'label'        => __('Duplicate'),
                'url'        => $this->getUrl('*/*/mass', ['action' => 'duplicate'])
            ])
            ->addItem('delete', [
                'label'        => __('Delete'),
                'url'        => $this->getUrl('*/*/mass', ['action' => 'delete']),
                'confirm'    => __('Are you sure?')
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
