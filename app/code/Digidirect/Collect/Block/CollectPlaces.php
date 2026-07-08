<?php

namespace Digidirect\Collect\Block;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Template\Context;

/**
 * CollectPlaces Block
 *
 * @package Digidirect\Collect\Block
 */
class CollectPlaces extends \Magento\Framework\View\Element\Template
{
    /**
     * CollectHelper
     *
     * @var \Digidirect\Collect\Helper\Data
     */
    protected $_collectHelper;

    /**
     * @var \Digidirect\Collect\Model\StorageHandler
     */
    protected $_storageHandler;

    /**
     * CheckoutSession
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * Quote Item
     *
     * @var \Magento\Quote\Model\Quote\Item
     */
    protected $_quoteItem;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Magento\Catalog\Model\Product|null
     */
    protected $_product = null;

    /**
     * CollectPlaces constructor.
     *
     * @param \Digidirect\Collect\Helper\Data $collectHelper
     * @param \Digidirect\Collect\Model\StorageHandler $storageHandler
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Digidirect\Collect\Helper\Data $collectHelper,
        \Digidirect\Collect\Model\StorageHandler $storageHandler,
        \Magento\Checkout\Model\Session $checkoutSession,
        Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_collectHelper = $collectHelper;
        $this->_storageHandler = $storageHandler;
        $this->_checkoutSession = $checkoutSession;
        $this->_registry = $registry;
        $this->_product = $this->_registry->registry('current_product');
    }

    /**
     * GetDistanceList
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDistanceList()
    {
        /** @var $select \Magento\Framework\View\Element\Html\Select */
        $select = $this->getLayout()->createBlock(
            Select::class,
            'collect_distance_list',
            [
                'data' => [
                    'id' => 'collect_distance',
                    'name' => 'collect_distance'
                ]
            ]
        );

        $select->setOptions($this->_collectHelper->getDistanceOptions());

        return $select->toHtml();
    }

    /**
     * GetPlacesUrl
     *
     * @param array $params
     * @return string
     */
    public function getPlacesUrl($params = [])
    {
        return $this->_collectHelper->getPlacesUrl($params);
    }

    /**
     * IsCollectPlacePreset
     *
     * @return bool
     */
    public function isCollectPlacePreset()
    {
        if ($this->getRequest()->getParam('collect_place_id') &&
            $this->getRequest()->getParam('collect_place_storage_name')
        ) {
            return true;
        }

        return false;
    }

    /**
     * GetPresetCollectPlace
     *
     * @return bool|\Digidirect\Collect\Api\Data\CollectPlaceInterface
     */
    public function getPresetCollectPlace()
    {
        if ($this->isCollectPlacePreset()) {
            return $this->_storageHandler->getCollectPlaceById(
                $this->getRequest()->getParam('collect_place_id'),
                $this->getRequest()->getParam('collect_place_storage_name')
            );
        }

        return false;
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
     * GetQuoteItem
     *
     * @return \Magento\Quote\Model\Quote\Item|null
     */
    public function getQuoteItem()
    {
        if (($quoteItemId = $this->getRequest()->getParam('id')) && !$this->_quoteItem) {
            $this->_quoteItem = $this->_checkoutSession->getQuote()->getItemById($quoteItemId);
        }

        return $this->_quoteItem;
    }

    /**
     * GetProduct
     *
     * @return \Magento\Catalog\Model\Product | null
     */
    public function getProduct()
    {
        return $this->_product;
    }

    /**
     * Show collect form
     *
     * @return bool
     */
    public function showCollect()
    {
        return $this->getCollectHelper()->isCollectEnable()
            && $this->getCollectHelper()->isFullVariation()
            && (!$this->getProduct() || !$this->getProduct()->isVirtual());
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if ($this->showCollect()) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * @return bool
     */
    public function isEnableSingleStoreInCartRestriction()
    {
        return $this->_collectHelper->isEnableSingleStoreInCartRestriction();
    }

    /**
     * @return bool
     */
    public function hasDeliveryItemInCart()
    {
        return $this->_collectHelper->hasDeliveryItemInCart();
    }

    /**
     * @return bool
     */
    public function hasCollectItemInCart()
    {
        return $this->_collectHelper->hasCollectItemInCart();
    }

    /**
     * It is needed for plugins.
     * @return bool
     */
    public function getIsPostcodeFieldNeeded()
    {
        return $this->getData('is_postcode_field_needed');
    }
}
