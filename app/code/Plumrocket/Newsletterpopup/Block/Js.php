<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Block;

use Magento\Cms\Model\Page;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Plumrocket\Base\ViewModel\Catalog\CurrentProductRetriever;
use Plumrocket\Newsletterpopup\Helper\Config;
use Plumrocket\Newsletterpopup\Helper\Data;
use Plumrocket\Newsletterpopup\Model\Config\Source\Cookies;
use Plumrocket\Newsletterpopup\Model\Config\Source\Show;
use Plumrocket\Newsletterpopup\ViewModel\Popup\GetActivePopupIds;

/**
 * Class Js. Block
 */
class Js extends Template
{
    /**
     * @var \Plumrocket\Newsletterpopup\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $_cmsPage;

    /**
     * @var bool
     */
    private $_disableThis = false;

    /**
     * @var \Plumrocket\Base\ViewModel\Catalog\CurrentProductRetriever
     */
    private $currentProductRetriever;

    /**
     * @var \Plumrocket\Newsletterpopup\Helper\Config
     */
    private $config;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @var \Plumrocket\Newsletterpopup\ViewModel\Popup\GetActivePopupIds
     */
    private $getActivePopupIds;

    /**
     * @param \Magento\Framework\View\Element\Template\Context              $context
     * @param \Plumrocket\Newsletterpopup\Helper\Data                       $dataHelper
     * @param \Magento\Framework\Registry                                   $registry
     * @param \Magento\Cms\Model\Page                                       $cmsPage
     * @param \Plumrocket\Base\ViewModel\Catalog\CurrentProductRetriever    $currentProductRetriever
     * @param \Plumrocket\Newsletterpopup\Helper\Config                     $config
     * @param \Magento\Framework\Serialize\SerializerInterface              $serializer
     * @param \Plumrocket\Newsletterpopup\ViewModel\Popup\GetActivePopupIds $getActivePopupIds
     * @param array                                                         $data
     */
    public function __construct(
        Context $context,
        Data $dataHelper,
        Registry $registry,
        Page $cmsPage,
        CurrentProductRetriever $currentProductRetriever,
        Config $config,
        SerializerInterface $serializer,
        GetActivePopupIds $getActivePopupIds,
        array $data = []
    ) {
        $this->_dataHelper = $dataHelper;
        $this->_registry = $registry;
        $this->_cmsPage = $cmsPage;
        parent::__construct($context, $data);
        $this->currentProductRetriever = $currentProductRetriever;
        $this->config = $config;
        $this->serializer = $serializer;
        $this->getActivePopupIds = $getActivePopupIds;
    }

    /**
     * Disable rendering if module is disabled.
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (! $this->config->isModuleEnabled() || $this->_disableThis) {
            return '';
        }
        return parent::_toHtml();
    }

    public function getPopupArea()
    {
        if (! $this->config->isModuleEnabled()) {
            return Show::ON_ALL_PAGES;
        }

        $request = $this->getRequest();
        $controller = $request->getControllerName();
        $action = $request->getActionName();
        $route = $request->getRouteName();

        if (($route === 'cms' && $controller === 'index' && $action === 'index')
            || ($route === 'privatesales' && $controller === 'homepage' && $action === 'index')
            || ($route === 'catalog' && $controller === 'category' && $action === 'homepage')
        ) {
            return Show::ON_HOME_PAGE;
        } elseif ($route === 'catalog' && $controller === 'category') { //  && $action == 'view'
            return Show::ON_CATEGORY_PAGES;
        } elseif ($route === 'catalog' && $controller === 'product') { //  && $action == 'view'
            return Show::ON_PRODUCT_PAGES;
        } elseif ($route === 'cms') {
            return Show::ON_CMS_PAGES;
        } elseif ($route === 'customer' && $controller === 'account') {
            return Show::ON_ACCOUNT_PAGES;
        }

        return Show::ON_ALL_PAGES;
    }

    public function isEnableAnalytics()
    {
        return $this->config->isModuleEnabled() && $this->config->isGoogleAnalyticsEnabled();
    }

    public function disable()
    {
        $this->_disableThis = true;
    }

    public function getActionUrl()
    {
        return $this->_dataHelper->validateUrl($this->getUrl('prnewsletterpopup/index/subscribe'));
    }

    public function getCancelUrl()
    {
        return $this->_dataHelper->validateUrl($this->getUrl('prnewsletterpopup/index/cancel'));
    }

    public function getBlockUrl()
    {
        return $this->_dataHelper->validateUrl($this->getUrl('prnewsletterpopup/index/block'));
    }

    /**
     * Get URL for loading popup html, css, and configs.
     *
     * @since 4.6.0
     * @return string
     */
    public function getLoadPopupUrl(): string
    {
        return $this->_dataHelper->validateUrl($this->getUrl('prnewsletterpopup/index/popup'));
    }

    public function getHistoryUrl()
    {
        return $this->_dataHelper->validateUrl($this->getUrl('prnewsletterpopup/index/history'));
    }

    /**
     * Get settings for js.
     *
     * @return array
     */
    public function getGlobalSettings(): array
    {
        return [
            'enable_analytics' => (int) $this->config->isGoogleAnalyticsEnabled(),
            'googleTagManagerEnabled' => $this->config->isGtmTrackingEnabled(),
            'area' => $this->getPopupArea(),
            'cmsPage' => (string) $this->_cmsPage->getIdentifier(),
            'categoryId' => ($category = $this->_registry->registry('current_category'))? $category->getId() : 0,
            'productId' => $this->currentProductRetriever->getId(),
            'action_url' => $this->getActionUrl(),
            'subscribeUrl' => $this->getActionUrl(),
            'cancel_url' => $this->getCancelUrl(),
            'block_url' => $this->getBlockUrl(),
            'loadPopupUrl' => $this->getLoadPopupUrl(),
            'history_url' => $this->getHistoryUrl(),
            'activePopupIds' => $this->getActivePopupIds->execute(),
            'isGlobalCookieUsage' => $this->config->getCookieUsage() === Cookies::GLOBAL
        ];
    }

    /**
     * Get serialized settings for js.
     *
     * @return string
     */
    public function getJsonConfig(): string
    {
        return $this->serializer->serialize($this->getGlobalSettings());
    }
}
