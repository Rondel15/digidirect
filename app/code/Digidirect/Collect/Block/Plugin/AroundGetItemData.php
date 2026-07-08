<?php

namespace Digidirect\Collect\Block\Plugin;

/**
 * Class AfterGetConfigureUrl
 * @package Digidirect\Collect\Block\Plugin\Cart\Item\Renderer\Actions\Edit
 */
class AroundGetItemData
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
     * AroundGetItemData constructor.
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
     * AroundGetItemData
     *
     * @param \Magento\Checkout\CustomerData\DefaultItem $subject
     * @param \Closure $proceed
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetItemData(
        \Magento\Checkout\CustomerData\DefaultItem $subject,
        $proceed,
        \Magento\Quote\Model\Quote\Item $quoteItem
    ) {
        $result = $proceed($quoteItem);

        if ($quoteItem->getCollectPlaceId() &&
            $this->_collectHelper->isCollectEnable() &&
            isset($result['configure_url'])
        ) {
            $result['configure_url'] = $this->_urlBuilder->getUrl(
                'checkout/cart/configure',
                [
                    'id' => $quoteItem->getId(),
                    'product_id' => $quoteItem->getProduct()->getId(),
                    'collect_place_id' => $quoteItem->getCollectPlaceId(),
                    'collect_place_storage_name' => $quoteItem->getCollectPlaceStorageName()
                ]
            );
        }

        return $result;
    }
}
