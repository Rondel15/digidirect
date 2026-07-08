<?php

namespace Digidirect\Feed\Block\Adminhtml\Rule;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Grid\Extended as ExtendedGrid;
use Magento\Backend\Helper\Data as BackendHelper;
use Digidirect\Feed\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;

class Grid extends ExtendedGrid
{
    /**
     * @var RuleCollectionFactory
     */
    protected $ruleCollectionFactory;

    /**
     * Grid constructor.
     * @param Context $context
     * @param BackendHelper $backendHelper
     * @param RuleCollectionFactory $ruleCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        BackendHelper $backendHelper,
        RuleCollectionFactory $ruleCollectionFactory,
        array $data = []
    ) {
        $this->ruleCollectionFactory = $ruleCollectionFactory;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('feed_rule_grid');
        $this->setDefaultSort('name');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareCollection()
    {
        $collection = $this->ruleCollectionFactory->create();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn('rule_id', [
            'header' => __('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'rule_id',
        ]);

        $this->addColumn('name', [
            'header' => __('Name'),
            'align' => 'left',
            'index' => 'name',
        ]);

        $this->addColumn('conditions', [
            'header' => __('Conditions'),
            'align' => 'left',
            'filter' => false,
            'sortable' => false,
            'renderer' => '\Digidirect\Feed\Block\Adminhtml\Rule\Renderer\Conditions',
        ]);

        $this->addColumn('action', [
            'header' => __('Action'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => [
                [
                    'caption' => __('Edit'),
                    'url' => ['base' => '*/*/edit'],
                    'field' => 'id',
                ],
                [
                    'caption' => __('Duplicate'),
                    'url' => ['base' => '*/*/duplicate'],
                    'field' => 'id',
                ],
                [
                    'caption' => __('Export'),
                    'url' => ['base' => '*/*/export'],
                    'field' => 'id',
                ],
                [
                    'caption' => __('Delete'),
                    'url' => ['base' => '*/*/delete'],
                    'field' => 'id',
                    'confirm' => __('Are you sure?'),
                ],
            ],
            'filter' => false,
            'sortable' => false,
            'is_system' => true,
        ]);

        return parent::_prepareColumns();
    }

    /**
     * {@inheritdoc}
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['id' => $row->getId()]);
    }
}
