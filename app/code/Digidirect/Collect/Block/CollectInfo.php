<?php

namespace Digidirect\Collect\Block;

use Digidirect\Collect\Helper\Data as CollectHelper;
use Magento\Checkout\Block\Cart\Additional\Info;

class CollectInfo extends Info
{
    /**
     * @var \Digidirect\Collect\Model\StorageHandler
     */
    protected $_storageHandler;

    /**
     * CollectHelper
     *
     * @var \Digidirect\Collect\Helper\Data
     */
    protected $_collectHelper;

    /**
     * CollectInfo constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Digidirect\Collect\Model\StorageHandler $storageHandler
     * @param CollectHelper $collectHelper
     * @param [] $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Digidirect\Collect\Model\StorageHandler $storageHandler,
        \Digidirect\Collect\Helper\Data $collectHelper,
        array $data
    ) {
        parent::__construct($context, $data);
        $this->_storageHandler = $storageHandler;
        $this->_collectHelper = $collectHelper;
    }

    /**
     * IsItemCollect
     *
     * @return bool
     */
    public function isItemCollect()
    {
        if (!$this->getItem()->getCollectPlaceId()) {
            return false;
        }

        return true;
    }

    /**
     * Get Change Place Url
     *
     * @return string
     */
    public function getChangePlaceUrl()
    {
        return $this->_collectHelper->getChangePlaceUrl();
    }

    /**
     * Get Deliver Instead Url
     *
     * @param bool $withQuoteItemId
     * @return string
     */
    public function getDeliverInsteadUrl($withQuoteItemId = true)
    {
        return $this->_collectHelper->getDeliverInsteadUrl(($withQuoteItemId ? $this->getItem()->getId() : null));
    }

    /**
     * Get CollectPlaceName
     *
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @return string
     */
    public function getCollectPlaceName($quoteItem)
    {
        $collectPlace = $this->_storageHandler->getCollectPlaceById(
            $quoteItem->getCollectPlaceId(),
            $quoteItem->getCollectPlaceStorageName()
        );

        if ($collectPlace) {
            return $collectPlace->getName();
        }

        return '';
    }

    /**
     * GetCollectHelper
     *
     * @return \Digidirect\Collect\Helper\Data
     */
    public function getCollectHelper()
    {
        return $this->_collectHelper;
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->getItem()->getProduct();
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if ($this->getCollectHelper()->isCollectEnable()
            && $this->_collectHelper->isFullVariation()
            && (!$this->getProduct() || !$this->getProduct()->isVirtual())
        ) {
            return parent::_toHtml();
        }
        return '';
    }
}
