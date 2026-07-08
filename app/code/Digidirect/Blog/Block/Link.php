<?php

namespace Digidirect\Blog\Block;

use Digidirect\Blog\Helper\Data;
use Digidirect\Blog\Model\UrlModel;
use Magento\Framework\App\DefaultPathInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Link
 */
class Link extends \Magento\Framework\View\Element\Html\Link\Current
{
    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var UrlModel
     */
    protected $urlModel;

    /**
     * @var mixed
     */
    protected $url;

    /**
     * Link constructor.
     *
     * @param Context $context
     * @param DefaultPathInterface $defaultPath
     * @param Data $dataHelper
     * @param UrlModel $urlModel
     * @param array $data
     * @param UrlInterface $url
     */
    public function __construct(
        Context $context,
        DefaultPathInterface $defaultPath,
        Data $dataHelper,
        UrlModel $urlModel,
        array $data = [],
        \Magento\Framework\UrlInterface $url = null
    ) {
        parent::__construct($context, $defaultPath, $data);
        $this->dataHelper = $dataHelper;
        $this->urlModel = $urlModel;
        $this->url = $url ?: ObjectManager::getInstance()->get(UrlInterface::class);
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->urlModel->getBlogListUrl();
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->dataHelper->getGeneralSettingsConfig('top_menu_title');
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->dataHelper->isModuleEnabled()) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * @return bool|int|null
     */
    public function getCacheLifetime()
    {
        $cacheLifetime = parent::getCacheLifetime();
        if (!$cacheLifetime) {
            $cacheLifetime = 86400;
        }

        return $cacheLifetime;
    }

    /**
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $cacheKey = parent::getCacheKeyInfo();
        $cacheKey['current_item'] = $this->getHref();
        $cacheKey['nil'] = $this->getNameInLayout();
        $cacheKey['request_params'] = json_encode($this->getRequest()->getParams());
        $cacheKey['is_module_enabled'] = $this->dataHelper->isModuleEnabled();
        $cacheKey['is_current'] = $this->isCurrent();
        return $cacheKey;
    }

    /**
     * @return bool
     */
    public function isCurrent()
    {
        return $this->getHref() == $this->url->getCurrentUrl();
    }
}
