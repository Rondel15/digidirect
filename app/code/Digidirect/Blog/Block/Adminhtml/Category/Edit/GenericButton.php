<?php

namespace Digidirect\Blog\Block\Adminhtml\Category\Edit;

use Digidirect\Blog\Api\Data\CategoryInterface;
use Digidirect\Blog\Model\CurrentStoreFetcher;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

/**
 * Class GenericButton
 */
class GenericButton
{
    /**
     * Request object
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * Registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Context $context,
        Registry $registry
    ) {
        $this->urlBuilder = $context->getUrlBuilder();
        $this->registry = $registry;
        $this->request = $context->getRequest();
    }

    /**
     * Return the current category item Id.
     *
     * @return int|null
     */
    public function getCategoryId()
    {
        $item = $this->getCurrentCategoryItem();
        return $item ? $item->getId() : null;
    }

    /**
     * @return mixed
     */
    public function getCurrentCategoryItem()
    {
        return $this->registry->registry(CategoryInterface::CURRENT_ITEM);
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->urlBuilder->getUrl($route, $params);
    }

    /**
     * @return mixed
     */
    protected function getCurrentStore()
    {
        return $this->request->getParam(CurrentStoreFetcher::PARAM_STORE);
    }
}
