<?php

namespace Digidirect\Collect\Block\Adminhtml\Order\View\Tab;

use Magento\Backend\Block\Widget\Tab\TabInterface;

/**
 * Class CollectStores
 *
 * @package Digidirect\Collect\Block\Adminhtml\Order\View\Tab
 */
class CollectStores extends \Magento\Backend\Block\Template implements TabInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Digidirect\Collect\Helper\Data
     */
    protected $_helper;

    /**
     * @var array
     */
    protected $_orderItems = [];

    /**
     * @var string
     */
    protected $_template = 'Digidirect_Collect::order/view/tab/collect_stores.phtml';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Digidirect\Collect\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Digidirect\Collect\Helper\Data $helper,
        array $data = []
    ) {
        if (isset($data['template'])) {
            $this->setTemplate($data['template']);
            unset($data['template']);
        }
        $this->_coreRegistry = $registry;
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Click & Collect Stores');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Click & Collect Stores');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return !$this->_isDisplayTab();
    }

    /**
     * Get Tab Class
     *
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax only';
    }

    /**
     * Get Class
     *
     * @return string
     */
    public function getClass()
    {
        return $this->getTabClass();
    }

    /**
     * Get Tab Url
     *
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('digidirectcollect/order/collectstores', ['_current' => true]);
    }

    /**
     * Get Order items with corresponding Collect-store.
     *
     * @return array
     */
    public function getItems()
    {
        $items = $this->_getOrderItems();
        $itemsWithPlace = [];

        foreach ($items as $orderItem) {
            /** @var \Magento\Sales\Model\Order\Item $orderItem * */
            if ($collectPlaceId = $orderItem->getCollectPlaceId()) {
                $collectPlace = $this->_helper->getCollectPlaceById(
                    $collectPlaceId,
                    $orderItem->getCollectPlaceStorageName()
                );
                $itemsWithPlace[] = [
                    'product' => $orderItem,
                    'store' => $collectPlace
                ];
            }
        }

        return $itemsWithPlace;
    }

    /**
     * If at least 2 different store places were picked for order items.
     *
     * @return bool
     */
    protected function _isDisplayTab()
    {
        $items = $this->_getOrderItems();
        $collectPlaceIds = [];

        foreach ($items as $orderItem) {
            /** @var \Magento\Sales\Model\Order\Item $orderItem * */
            if ($placeId = $orderItem->getCollectPlaceId()) {
                $collectPlaceIds[] = $placeId;
            }
        }

        return count(array_unique($collectPlaceIds)) > 1;
    }

    /**
     * @return array
     */
    protected function _getOrderItems()
    {
        if (empty($this->_orderItems)) {
            $this->_orderItems = $this->getOrder()->getAllVisibleItems();
        }

        return $this->_orderItems;
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $item
     * @return array
     */
    public function getOrderItemOptions($item)
    {
        $result = [];
        $options = $item->getProductOptions();
        if ($options) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['attributes_info'])) {
                $result = array_merge($result, $options['attributes_info']);
            }
        }

        return $result;
    }
}
