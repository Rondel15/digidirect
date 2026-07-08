<?php
namespace Digidirect\Utilities\Plugin\Catalog\Helper;

use Digidirect\Utilities\Helper\Image as ImageHelper;

class Image
{
    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * @param ImageHelper $imageHelper
     */
    public function __construct(
        ImageHelper $imageHelper
    ) {
        $this->imageHelper = $imageHelper;
    }

    /**
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Closure $proceed
     * @param \Magento\Catalog\Model\Product $product
     * @param string $imageTypeId
     * @param array $attributes
     * @return \Magento\Catalog\Helper\Image
     */
    public function aroundInit(
        \Magento\Catalog\Helper\Image $imageHelper,
        \Closure $proceed,
        $product,
        $imageTypeId,
        $attributes = []
    ) {
        /**
         * @var $image \Magento\Catalog\Helper\Image
         */
        $image = $proceed($product, $imageTypeId, $attributes);
        $quality = (int)$this->imageHelper->getImageQualityByType($imageTypeId);
        if ($quality) {
            $image->setQuality($quality);
        }
        return $image;
    }
}
