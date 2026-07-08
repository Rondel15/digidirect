<?php
namespace Digidirect\RelatedProduct\Plugin\Magento\Catalog\Model\Product\Gallery;

use Digidirect\RelatedProduct\Setup\InstallSchema;
use Digidirect\RelatedProduct\Helper\RangeProduct as RangeProductHelper;
use Magento\Catalog\Model\Product\Gallery\CreateHandler as OriginalCreateHandler;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Gallery as ResourceModel;

/**
 * Class CreateHandler
 * @author Digidirect team
 * @package Digidirect\RelatedProduct\Plugin\Magento\Catalog\Model\Product\Gallery
 */
class CreateHandler
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Gallery
     */
    protected $resourceModel;

    /**
     * @var RangeProductHelper
     */
    protected $rangeProductHelper;

    /**
     * CreateHandler constructor.
     * @param ResourceModel $resourceModel
     * @param RangeProductHelper $rangeProductHelper
     */
    public function __construct(ResourceModel $resourceModel, RangeProductHelper $rangeProductHelper)
    {
        $this->resourceModel = $resourceModel;
        $this->rangeProductHelper = $rangeProductHelper;
    }

    /**
     * @param OriginalCreateHandler $mediaGalleryCreateHandler
     * @param Product $product
     * @return Product
     */
    public function afterExecute(OriginalCreateHandler $mediaGalleryCreateHandler, Product $product)
    {
        $mediaGallery = $product->getData('media_gallery');
        if (isset($mediaGallery['images']) && !isset($mediaGallery['duplicate'])) {
            $this->saveCustomData($mediaGallery['images'], $product);
        }
        return $product;
    }

    /**
     * @param array $images
     * @param Product $product
     * @return $this
     */
    protected function saveCustomData($images, $product)
    {
        if (!is_array($images) || !$this->rangeProductHelper->isRangeProduct($product)) {
            return $this;
        }
        foreach ($images as $image) {
            if (!isset($image['media_type']) || $image['media_type'] !== 'image') {
                continue;
            }
            $removed = isset($image['removed']) ? (int)$image['removed'] : 0;
            if (isset($image['value_id']) && $removed) {
                $this->deleteRecord($image['value_id']);
                continue;
            }
            $this->saveRecord([
                'value_id'      => $image['value_id'] ?? 0,
                'store_id'      => (int)$product->getStoreId(),
                'featured_product_image' => $image['featured_product_image'] ?? 0,
            ]);
        }
        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    protected function saveRecord($data)
    {
        $this->resourceModel->saveDataRow(InstallSchema::CUSTOM_GALLERY_VALUE_TABLE, $data);
        return $this;
    }

    /**
     * @param int $valueId
     * @return $this
     */
    protected function deleteRecord($valueId)
    {
        $valueId = (int)$valueId;
        if ($valueId) {
            $condition = $this->resourceModel->getConnection()->quoteInto('value_id = ?', $valueId);
            $this->resourceModel->getConnection()->delete(InstallSchema::CUSTOM_GALLERY_VALUE_TABLE, $condition);
        }
        return $this;
    }
}
