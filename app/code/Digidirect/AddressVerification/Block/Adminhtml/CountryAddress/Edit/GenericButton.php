<?php
namespace Digidirect\AddressVerification\Block\Adminhtml\CountryAddress\Edit;

use Digidirect\AddressVerification\Api\Data\CountryAddressAttributeInterface;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

/**
 * Class GenericButton
 * @package Digidirect\AddressVerification\Block\Adminhtml\CountryAddress\Edit
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
     * Return the current post item Id.
     *
     * @return int|null
     */
    public function getId()
    {
        $item = $this->getCurrentItem();
        return $item ? $item->getId() : null;
    }

    /**
     * Get editable item
     *
     * @return \Digidirect\AddressVerification\Model\CountryAddressAttribute
     */
    public function getCurrentItem()
    {
        return $this->registry->registry(CountryAddressAttributeInterface::CURRENT_ITEM);
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
