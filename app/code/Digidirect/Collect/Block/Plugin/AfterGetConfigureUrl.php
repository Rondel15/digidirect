<?php

namespace Digidirect\Collect\Block\Plugin;

/**
 * Class AfterGetConfigureUrl
 * @package Digidirect\Collect\Block\Plugin\Cart\Item\Renderer\Actions\Edit
 */
class AfterGetConfigureUrl
{
    /**
     * CollectHelper
     *
     * @var \Digidirect\Collect\Helper\Data
     */
    protected $_collectHelper;

    /**
     * UrlBuilder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * AfterGetConfigureUrl constructor.
     *
     * @param \Digidirect\Collect\Helper\Data $collectHelper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(
        \Digidirect\Collect\Helper\Data $collectHelper,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->_collectHelper = $collectHelper;
        $this->_urlBuilder = $urlBuilder;
    }

    /**
     * AfterGetConfigureUrl
     *
     * @param \Magento\Checkout\Block\Cart\Item\Renderer\Actions\Edit $subject
     * @param string $url
     * @return string
     */
    public function afterGetConfigureUrl(
        \Magento\Checkout\Block\Cart\Item\Renderer\Actions\Edit $subject,
        string $url
    ) {
        /** @var $quoteItem \Magento\Quote\Model\Quote\Item*/
        $quoteItem = $subject->getItem();

        if ($quoteItem->getCollectPlaceId() && $this->_collectHelper->isCollectEnable()) {
            $params = [
                'id' => $subject->getItem()->getId(),
                'product_id' => $subject->getItem()->getProduct()->getId(),
                'collect_place_id' => $quoteItem->getCollectPlaceId(),
                'collect_place_storage_name' => $quoteItem->getCollectPlaceStorageName()
            ];

            $url = $this->_urlBuilder->getUrl('checkout/cart/configure', $params);
        }

        return $url;
    }
}
