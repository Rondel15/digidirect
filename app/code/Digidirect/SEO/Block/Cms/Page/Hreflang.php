<?php

namespace Digidirect\SEO\Block\Cms\Page;

use Digidirect\SEO\Helper\HreflangTags;
use Digidirect\SEO\Model\Hreflang\HreflangFactory;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Hreflang
 */
class Hreflang extends \Magento\Framework\View\Element\Template
{
    /**
     * @var HreflangTags
     */
    protected $hreflangHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var HreflangFactory
     */
    protected $hreflangFactory;

    /**
     * Hreflang constructor.
     * @param Context $context
     * @param HreflangTags $hreflangHelper
     * @param StoreManagerInterface $storeManager
     * @param HreflangFactory $hreflangFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        HreflangTags $hreflangHelper,
        StoreManagerInterface $storeManager,
        HreflangFactory $hreflangFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->hreflangHelper = $hreflangHelper;
        $this->storeManager = $storeManager;
        $this->hreflangFactory = $hreflangFactory;
    }

    /**
     * @return bool
     */
    public function isHreflangTagEnable()
    {
        return $this->hreflangHelper->isHreflangTagEnabled();
    }

    /**
     * @return array
     */
    public function getActiveStoreList()
    {
        $stores = $this->storeManager->getStores();
        $activeStores = [];
        foreach ($stores as $store) {
            if ($store->getIsActive()) {
                $activeStores[] = $store;
            }
        }
        return $activeStores;
    }

    /**
     * @return mixed|null
     */
    public function getHreflangObject()
    {
        $hreflangObjectName = $this->hreflangHelper->getHreflangCodeSelection();
        return $this->hreflangFactory->create($hreflangObjectName);
    }

    /**
     * @param $store
     * @return mixed
     */
    public function getHreflang($store)
    {
        $hreflangObject = $this->getHreflangObject();
        $lang = $hreflangObject->getHreflangData($store);

        return $lang;
    }

    /**
     * @param $store
     * @return mixed
     */
    public function getStoreUrl($store)
    {
        $hreflangObject = $this->getHreflangObject();
        $url = $hreflangObject->getHreflangUrl($store);

        return $url;
    }
}
