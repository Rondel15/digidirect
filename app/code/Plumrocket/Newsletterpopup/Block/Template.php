<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Block;

use Magento\Framework\View\Element\Template as ViewTemplate;
use Magento\Framework\View\Element\Template\Context;
use Plumrocket\Newsletterpopup\Helper\Config;
use Plumrocket\Newsletterpopup\Helper\Data;
use Plumrocket\Newsletterpopup\Model\Popup\Space;
use Plumrocket\Newsletterpopup\Model\Popup\Variable\Placeholder;
use Plumrocket\Newsletterpopup\Model\Preview;
use Plumrocket\Newsletterpopup\ViewModel\Popup\JsConfig as PopupJsConfig;

class Template extends ViewTemplate
{
    /**
     * @var \Plumrocket\Newsletterpopup\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\Popup\Space
     */
    protected $_space;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var bool
     */
    protected $_layoutBased = false;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\Preview
     */
    private $preview;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\Popup\Variable\Placeholder
     */
    private $variablePlaceholder;

    /**
     * @var \Plumrocket\Newsletterpopup\ViewModel\Popup\JsConfig
     */
    private $popupJsConfig;

    /**
     * @var \Plumrocket\Newsletterpopup\Helper\Config
     */
    private $config;

    /**
     * @param \Magento\Framework\View\Element\Template\Context             $context
     * @param \Plumrocket\Newsletterpopup\Helper\Data                      $dataHelper
     * @param \Plumrocket\Newsletterpopup\Model\Popup\Space                $space
     * @param \Plumrocket\Newsletterpopup\Model\Preview                    $preview
     * @param \Plumrocket\Newsletterpopup\Model\Popup\Variable\Placeholder $variablePlaceholder
     * @param \Plumrocket\Newsletterpopup\ViewModel\Popup\JsConfig         $popupJsConfig
     * @param \Plumrocket\Newsletterpopup\Helper\Config                    $config
     * @param array                                                        $data
     */
    public function __construct(
        Context $context,
        Data $dataHelper,
        Space $space,
        Preview $preview,
        Placeholder $variablePlaceholder,
        PopupJsConfig $popupJsConfig,
        Config $config,
        array $data = []
    ) {
        $this->_dataHelper = $dataHelper;
        $this->_space = $space;
        $this->_storeManager = $context->getStoreManager();
        $this->preview = $preview;
        $this->variablePlaceholder = $variablePlaceholder;
        $this->popupJsConfig = $popupJsConfig;
        $this->config = $config;

        parent::__construct($context, $data);
        $this->_cacheInit();
    }

    /**
     * Used for overriding in preview mode
     *
     * @return bool
     */
    protected function _isEnabled()
    {
        return $this->config->isModuleEnabled();
    }

    protected function _cacheInit()
    {
        if ($this->config->isModuleEnabled() && ! $this->preview->isEnabled()) {
            $popup = $this->getPopup();
            if ($popup && !$popup->getIsTemplate()) {
                $popupId = $popup->getId();
                if ($popupId > 0) {
                    $storeCode = $this->_storeManager->getStore()->getCode();

                    if ($popup->useCurrentProduct()) {
                        // Use current product as part of cache only for popup with enabled option
                        // in order to optimize cache
                        $cacheKey = "{$popupId}_{$storeCode}_{$this->getProductCacheId()}";
                    } else {
                        $cacheKey = "{$popupId}_{$storeCode}";
                    }

                    $this->addData(
                        [
                            'cache_lifetime' => 86400, // (seconds) data lifetime in the cache
                            'cache_tags'     => ['prnewsletterpopup_' . $popupId],
                            'cache_key'      => $cacheKey
                        ]
                    );
                }
            }
        }
    }

    public function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->_isEnabled() && !$this->_layoutBased) {
            $this->setTemplate('popup.phtml')
                ->setChild(
                    'popup.body',
                    $this->getLayout()->createBlock(Popup::class)
                );
        }
    }

    protected function _toHtml()
    {
        if (!$this->_isEnabled()
            // if not found or in account pages or popup included in array of locked popups
            || ($this->getPopup()->getId() == 0)
        ) {
            return '';
        }

        if ($this->variablePlaceholder->hasProductPlaceholder($this->getPopup()) &&
            ! $this->getPopup()->getDefaultProduct()
        ) {
            $missingProductInfoNotice = '<div class="newspopup-preview-notice" style="display: none">' . __(
                'Notice: You have not entered the "Default Product SKU" in popup "Display Settings" tab. ' .
                'The Popup Preview is missing product info.'
            ) . '</div>';
        } else {
            $missingProductInfoNotice = '';
        }

        return $missingProductInfoNotice . parent::_toHtml();
    }

    /**
     * @return \Plumrocket\Newsletterpopup\Model\Popup
     */
    public function getPopup()
    {
        return $this->_dataHelper->getCurrentPopup();
    }

    /**
     * @return string
     */
    public function getJsonConfig()
    {
        return $this->popupJsConfig->getJson($this->getPopup());
    }

    /**
     * @return int
     */
    public function getProductCacheId(): int
    {
        return (int) $this->_getData('currentProductId');
    }
}
