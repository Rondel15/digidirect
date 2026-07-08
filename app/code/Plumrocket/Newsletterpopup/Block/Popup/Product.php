<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Popup;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Image;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Plumrocket\Newsletterpopup\Model\Popup;

/**
 * @method $this setPopup(Popup $popup)
 * @method $this setProduct(ProductInterface $product)
 *
 * @method null|Popup            getPopup()
 * @method null|ProductInterface getProduct()
 */
class Product extends Template
{
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    private $imageHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Helper\Image                    $imageHelper
     * @param array                                            $data
     */
    public function __construct(
        Context $context,
        Image $imageHelper,
        array $data = []
    ) {
        $this->imageHelper = $imageHelper;
        parent::__construct($context, $data);
    }

    /**
     * Set default template
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        if (! $this->getTemplate()) {
            $this->setTemplate('Plumrocket_Newsletterpopup::popup/product.phtml');
        }

        return parent::_beforeToHtml();
    }

    /**
     * Disable output if product does not specified
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (! $this->getProduct()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * Check if show price, or show only discount
     *
     * @return bool
     */
    public function showPrice(): bool
    {
        return (bool) $this->getProduct()->getFinalPrice();
    }

    /**
     * @return string
     */
    public function getImageUrl()
    {
        return $this->imageHelper->init($this->getProduct(), 'product_small_image')
            ->resize(126, 126)
            ->getUrl();
    }
}
