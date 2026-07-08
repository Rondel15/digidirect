<?php
namespace Digidirect\AbstractEntity\Block\Adminhtml\AbstractEntity\Edit;

use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Magento\Backend\Block\Widget\Context;

abstract class GenericButton
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     */
    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * Return model ID
     *
     * @return int|null
     */
    public function getModelId()
    {
        return $this->context->getRequest()->getParam('id');
    }

    /**
     * Return model ID
     *
     * @return int|null
     */
    public function getSetId()
    {
        return $this->context->getRequest()->getParam(AbstractEntityInterface::ATTRIBUTE_SET_ID);
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
        $params[AbstractEntityInterface::ATTRIBUTE_SET_ID] = $this->getSetId();
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
