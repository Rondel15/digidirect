<?php

namespace Digidirect\Feed\Helper;

use Magento\Backend\Model\UrlInterface as BackendUrlInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Digidirect\Feed\Model\Feed;

class Data extends AbstractHelper
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var BackendUrlInterface
     */
    protected $backendUrl;

    /**
     * Data constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param BackendUrlInterface $backendUrl
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        BackendUrlInterface $backendUrl
    ) {
        $this->storeManager = $storeManager;
        $this->backendUrl = $backendUrl;

        parent::__construct($context);
    }

    /**
     * Feed delivery URL
     *
     * @param Feed $feed
     * @return string
     */
    public function getFeedDeliverUrl(Feed $feed)
    {
        return $this->storeManager->getStore()->getUrl('*/*/delivery', ['id' => $feed->getId()]);
    }

    /**
     * Feed preview URL
     *
     * @param Feed $feed
     * @return string
     */
    public function getFeedPreviewUrl(Feed $feed)
    {
        return $this->backendUrl->getUrl(
            '*/*/preview',
            [
                'id' => $feed->getId(),
                'skip' => 'rules'
            ]
        );
    }

    /**
     * Feed export URL
     *
     * @param Feed $feed
     * @return string
     */
    public function getFeedExportUrl(Feed $feed)
    {
        $url = $feed->getStore()->getBaseUrl() . 'digidirect_feed/export/execute';

        $parsedUrl = parse_url($url);

        if ($parsedUrl['host'] != $_SERVER['HTTP_HOST']) {
            $url = $this->backendUrl->getUrl('*/*/execute');
        }

        $url = strtok($url, '?');

        return $url;
    }

    /**
     * Feed progress URL
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getFeedProgressUrl()
    {
        $stateUrl = $this->backendUrl->getUrl('*/*/progress');
        $stateUrl = strtok($stateUrl, '?');

        return $stateUrl;
    }
}
