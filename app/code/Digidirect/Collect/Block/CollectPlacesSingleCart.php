<?php
namespace Digidirect\Collect\Block;

use Digidirect\Collect\Api\ApplyCollectPlaceInterface;
use Digidirect\Collect\Api\Data\CollectPlaceInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class CollectPlacesSingleCart
 * @package Digidirect\Collect\Block
 */
class CollectPlacesSingleCart extends CollectPlaces
{
    /**
     * @var ApplyCollectPlaceInterface
     */
    protected $applyCollectPlaceApi;

    /**
     * CollectPlacesSingleCart constructor.
     * @param \Digidirect\Collect\Helper\Data $collectHelper
     * @param \Digidirect\Collect\Model\StorageHandler $storageHandler
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ApplyCollectPlaceInterface $applyCollectPlaceApi
     * @param array $data
     */
    public function __construct(
        \Digidirect\Collect\Helper\Data $collectHelper,
        \Digidirect\Collect\Model\StorageHandler $storageHandler,
        \Magento\Checkout\Model\Session $checkoutSession,
        Context $context,
        \Magento\Framework\Registry $registry,
        ApplyCollectPlaceInterface $applyCollectPlaceApi,
        array $data = []
    ) {
        parent::__construct($collectHelper, $storageHandler, $checkoutSession, $context, $registry, $data);
        $this->applyCollectPlaceApi = $applyCollectPlaceApi;
    }

    /**
     * @return bool
     */
    public function showCollect()
    {
        return $this->getCollectHelper()->isCollectEnable()
            && $this->getCollectHelper()->isSingleCartVariation();
    }

    /**
     * @return CollectPlaceInterface|bool
     */
    public function getPresetCollectPlace()
    {
        return $this->applyCollectPlaceApi->getSelectedSingleCollectPlace();
    }
}
