<?php
namespace Digidirect\AbstractEntity\Block\Adminhtml\Attribute;

use Magento\Framework\App\ObjectManager;

class Grid extends \Magento\Eav\Block\Adminhtml\Attribute\Grid\AbstractGrid
{
    /**
     * @var \Digidirect\AbstractEntity\Model\ResourceModel\Eav\Attribute\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var string
     */
    protected $_module = 'digidirect_abstractentity';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Digidirect\AbstractEntity\Model\ResourceModel\Eav\Attribute\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        $collectionFactory = null,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory ?: ObjectManager::getInstance()->get(
            'Digidirect\AbstractEntity\Model\ResourceModel\Eav\Attribute\CollectionFactory'
        );
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareCollection()
    {
        $collection = $this->collectionFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare customer attributes grid columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->addColumn(
            'is_global',
            [
                'header' => __('Scope'),
                'sortable' => true,
                'align' => 'center',
                'index' => 'is_global',
                'header_css_class' => 'col-is_global',
                'column_css_class' => 'col-is_global',
                'type' => 'options',
                'options' => [
                    '0' => __('Store View'),
                    '1' => __('Website'),
                    '2' => __('Global'),
                ],
            ]
        );

        return $this;
    }
}
