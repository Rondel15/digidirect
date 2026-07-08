<?php

namespace Digidirect\ProductOverlay\Plugin\Catalog\Product;

/**
 * Class Overlay
 *
 * @package Digidirect\ProductOverlay\Plugin\Catalog\Product
 */
class Overlay
{
    /**
     * @var \Digidirect\ProductOverlay\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var array
     */
    protected $data;

    /**
     * Overlay constructor.
     *
     * @param \Digidirect\ProductOverlay\Helper\Data $helper
     * @param \Magento\Framework\App\RequestInterface $request
     * @param array $data
     */
    public function __construct(
        \Digidirect\ProductOverlay\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->_request = $request;
        $this->data = $data;
    }

    /**
     * @param \Magento\Catalog\Block\Product\Image $subject
     * @param mixed $result
     * @return string
     */
    public function afterToHtml(
        \Magento\Catalog\Block\Product\Image $subject,
        $result
    ) {
        $productId = $subject->getProductId();
        if (!$productId && ($product = $subject->getProduct())) {
            $productId = $subject->getProduct()->getId();
        }

        if ($productId && $this->_request->getFullActionName() == 'catalog_category_view'
            || $this->needToProcessOverlay($subject)
        ) {
            $result .= $this->_helper->renderProductOverlay($productId, 'category');
        }
        return $result;
    }

    /**
     * @param \Magento\Catalog\Block\Product\Image $subject
     * @return bool
     */
    protected function needToProcessOverlay($subject)
    {
        if (!$this->processHandle()) {
            return false;
        }
        $instances = $this->data['instances'] ?? [];
        foreach ($instances as $instance) {
            if ($subject instanceof $instance) {
                return false;
            }
        }
        $excludeImages = $this->data['exclude_images'] ?? [];
        if ($subject->getImageId()) {
            foreach ($excludeImages as $excludeImage) {
                if(empty($excludeImage))
                {
                    return false;
                }
                if (trim($excludeImage) == $subject->getImageId()) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Exclude processing overlays on wishlist page - https://digidirect.tpondemand.com/entity/161341
     *
     * @return bool
     */
    protected function processHandle()
    {
        $handle = trim($this->getHandle());
        $excludeHandles = $this->data['exclude_handles'] ?? [];
        foreach ($excludeHandles as $excludeHandle) {
            if (trim($excludeHandle) == $handle) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return string
     */
    protected function getHandle()
    {
        return $this->_request->getFullActionName();
    }
}
