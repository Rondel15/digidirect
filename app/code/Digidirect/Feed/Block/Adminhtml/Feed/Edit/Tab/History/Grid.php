<?php

namespace Digidirect\Feed\Block\Adminhtml\Feed\Edit\Tab\History;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Grid\Extended as ExtendedGrid;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Framework\Registry;
use Digidirect\Feed\Model\ResourceModel\Feed\History\CollectionFactory;

class Grid extends ExtendedGrid
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Grid constructor.
     * @param Context $context
     * @param BackendHelper $backendHelper
     * @param Registry $registry
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        BackendHelper $backendHelper,
        Registry $registry,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->collectionFactory = $collectionFactory;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('history_grid');
        $this->setUseAjax(true);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareCollection()
    {
        $model = $this->registry->registry('current_model');

        $collection = $this->collectionFactory->create()
            ->addFieldToFilter('feed_id', $model->getId())
            ->setOrder('created_at', 'desc')
            ->setOrder('history_id', 'desc');

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn('history_created_at', [
            'header' => __('Created At'),
            'index' => 'created_at',
            'type' => 'datetime',
        ]);

        $this->addColumn('history_type', [
            'header' => __('Type'),
            'index' => 'type',
        ]);

        $this->addColumn('history_title', [
            'header' => __('Title'),
            'index' => 'title',
        ]);

        $this->addColumn('history_message', [
            'header' => __('Message'),
            'index' => 'message',
            'renderer' => '\Digidirect\Feed\Block\Adminhtml\Feed\Edit\Tab\History\Grid\Renderer\Message',
        ]);

        return parent::_prepareColumns();
    }

    /**
     * {@inheritdoc}
     */
    public function getRowUrl($item)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/historyGrid', ['_current' => true]);
    }
}
