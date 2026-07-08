<?php

namespace Digidirect\Blog\ViewModel;

use Magento\Framework\Registry;
use Digidirect\Blog\Api\Data\CategoryInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Config\Model\Config\Backend\Image\Logo;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\Asset\Repository as AssetRepo;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\LocalizedException;
use Digidirect\Blog\Helper\LocalCode as LocalCodeHelper;
use Digidirect\Blog\Helper\Data as BlogConfigHelper;

class OpenGraphBlogCategory implements ArgumentInterface
{
    const OG_TYPE = 'website';
    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var UrlInterface
     */
    private $urlBuilder;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var AssetRepo
     */
    private $assetRepo;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var LocalCodeHelper
     */
    private $localCodeHelper;
    /**
     * @var BlogConfigHelper
     */
    private $configHelper;

    /**
     * OpenGraphBlogCategory constructor.
     * @param Registry $registry
     * @param UrlInterface $urlBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param AssetRepo $assetRepo
     * @param LoggerInterface $logger
     * @param LocalCodeHelper $localCodeHelper
     * @param BlogConfigHelper $configHelper
     */
    public function __construct(
        Registry $registry,
        UrlInterface $urlBuilder,
        ScopeConfigInterface $scopeConfig,
        AssetRepo $assetRepo,
        LoggerInterface $logger,
        LocalCodeHelper $localCodeHelper,
        BlogConfigHelper $configHelper
    ) {
        $this->registry = $registry;
        $this->urlBuilder = $urlBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->assetRepo = $assetRepo;
        $this->logger = $logger;
        $this->localCodeHelper = $localCodeHelper;
        $this->configHelper = $configHelper;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->registry->registry(CategoryInterface::CURRENT_ITEM);
    }

    /**
     * @return null|string
     */
    public function getTitle()
    {
        $currentCategory = $this->getCategory();
        if ($currentCategory) {
            $metaTitle = !empty($currentCategory->getMetaTitle())
                ? $currentCategory->getMetaTitle() : $currentCategory->getName();
        }
        return $metaTitle ?? ' ';
    }

    /**
     * @return string
     */
    public function getImage()
    {
        $url = '';
        $folderName = Logo::UPLOAD_DIR;
        $storeLogoPath = $this->scopeConfig->getValue(
            'design/header/logo_src',
            ScopeInterface::SCOPE_STORE
        );
        $path = $folderName . '/' . $storeLogoPath;
        $logoUrl = $this->urlBuilder
                ->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]) . $path;
        try {
            if ($storeLogoPath !== null) {
                return $logoUrl;
            }
            $url = $this->assetRepo->getUrlWithParams('images/logo.svg', ['_secure' => true]);
        } catch (LocalizedException $e) {
            $this->logger->critical($e);
        }
        return $url;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        $currentCategory = $this->getCategory();
        if ($currentCategory) {
            $metaDescription = $currentCategory->getMetaDescription();
        }
        return $metaDescription ?? ' ';
    }

    /**
     * @return string
     */
    public function getType()
    {
        return self::OG_TYPE;
    }

    /**
     * @return string
     */
    public function getCanonicalUrl()
    {
        $currentCategory = $this->getCategory();
        if ($currentCategory) {
            $pageUrl = $currentCategory->getViewUrl();
        }
        return !empty($pageUrl) ? $pageUrl : $this->urlBuilder->getCurrentUrl();
    }

    /**
     * @return string|null
     */
    public function getLocalCode()
    {
        return $this->localCodeHelper->getLocaleCode();
    }

    /**
     * @return array
     */
    public function getAlternativeLocaleCodes()
    {
        $storesLocaleCode = [];
        $currentCategory = $this->getCategory();
        if ($currentCategory && $this->configHelper->isEnableDisplayAlternatesLocalesTagCategoryPage()) {
            $currentLocalCode[] = $this->getLocalCode();
            $availableStoreIds = $currentCategory->getViewStores();
            $storesLocaleCode = $this->localCodeHelper->getUniqueStoreLocales($currentLocalCode, $availableStoreIds);
        }

        return $storesLocaleCode;
    }
}
