<?php
namespace Digidirect\Blog\Block\Adminhtml\Comment\Edit;

use Digidirect\Blog\Api\Data\CommentInterface;
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
     * Return the current comment item Id.
     *
     * @return int|null
     */
    public function getCommentId()
    {
        $item = $this->getCurrentCommentItem();
        return $item ? $item->getId() : null;
    }

    /**
     * @return mixed
     */
    public function getCurrentCommentItem()
    {
        return $this->registry->registry(CommentInterface::CURRENT_ITEM);
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
}
