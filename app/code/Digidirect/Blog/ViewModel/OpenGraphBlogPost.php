<?php

namespace Digidirect\Blog\ViewModel;

use Magento\Framework\Registry;
use Digidirect\Blog\Api\Data\PostInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Psr\Log\LoggerInterface;
use Digidirect\Blog\Helper\LocalCode as LocalCodeHelper;
use Digidirect\Blog\Helper\Data as BlogConfigHelper;

class OpenGraphBlogPost implements ArgumentInterface
{
    const OG_TYPE = 'article';
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
     * OpenGraphBlogPost constructor.
     * @param Registry $registry
     * @param UrlInterface $urlBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     * @param LocalCodeHelper $localCodeHelper
     * @param BlogConfigHelper $configHelper
     */
    public function __construct(
        Registry $registry,
        UrlInterface $urlBuilder,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger,
        LocalCodeHelper $localCodeHelper,
        BlogConfigHelper $configHelper
    ) {
        $this->registry = $registry;
        $this->urlBuilder = $urlBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->localCodeHelper = $localCodeHelper;
        $this->configHelper = $configHelper;
    }

    /**
     * @return \Digidirect\Blog\Model\Post|null
     */
    public function getPost()
    {
        return $this->registry->registry(PostInterface::CURRENT_ITEM);
    }

    /**
     * @return null|string
     */
    public function getTitle()
    {
        $currentPost = $this->getPost();
        if ($currentPost) {
            $metaTitle = !empty($currentPost->getMetaTitle())
                ? $currentPost->getMetaTitle() : $currentPost->getTitle();
        }
        return $metaTitle ?? ' ';
    }

    /**
     * @return string
     */
    public function getImage()
    {
        $urlImage = '';
        $currentPost = $this->getPost();
        if ($currentPost) {
            $urlImage = $currentPost->getMainImage();
        }
        return $urlImage;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        $currentPost = $this->getPost();
        if ($currentPost) {
            $metaDescription = $currentPost->getMetaDescription();
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
        $currentPost = $this->getPost();
        if ($currentPost) {
            $pageUrl = $currentPost->getViewUrl();
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
        $currentPost = $this->getPost();
        if ($currentPost && $this->configHelper->isEnableDisplayAlternatesLocalesTagPostPage()) {
            $currentLocalCode[] = $this->getLocalCode();
            $availableStoreIds = $currentPost->getAvailableStoresIds();
            $storesLocaleCode = $this->localCodeHelper->getUniqueStoreLocales($currentLocalCode, $availableStoreIds);
        }

        return $storesLocaleCode;
    }
}
