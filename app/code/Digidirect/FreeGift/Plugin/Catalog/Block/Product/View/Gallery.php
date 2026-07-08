<?php

namespace Digidirect\FreeGift\Plugin\Catalog\Block\Product\View;

use Digidirect\FreeGift\Api\RuleRepositoryInterface;
use Magento\Catalog\Helper\Image;
use Magento\Framework\Data\Collection;
use Magento\Framework\DataObject;

class Gallery
{
    /**
     * @var RuleRepositoryInterface
     */
    protected $_ruleRepository;

    /**
     * @var Image
     */
    protected $_imageHelper;

    /**
     * Gallery constructor.
     *
     * @param RuleRepositoryInterface $ruleRepository
     * @param Image $imageHelper
     */
    public function __construct(
        RuleRepositoryInterface $ruleRepository,
        Image $imageHelper
    ) {
        $this->_ruleRepository = $ruleRepository;
        $this->_imageHelper = $imageHelper;
    }

    /**
     * @param \Magento\Catalog\Block\Product\View\Gallery $subject
     * @param Collection $result
     * @return mixed
     */
    public function afterGetGalleryImages(
        \Magento\Catalog\Block\Product\View\Gallery $subject,
        $result
    ) {
        $product = $subject->getProduct();
        $promoItems = $this->_ruleRepository->getPromoItemsByProduct($product);
        foreach ($promoItems as $promoItem) {
            $image = new DataObject();
            $image->setData(
                'small_image_url',
                $this->_imageHelper->init($promoItem, 'product_page_image_small')
                    ->getUrl()
            );
            $image->setData(
                'medium_image_url',
                $this->_imageHelper->init($promoItem, 'product_page_image_medium')
                    ->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)
                    ->getUrl()
            );
            $image->setData(
                'large_image_url',
                $this->_imageHelper->init($promoItem, 'product_page_image_large')
                    ->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)
                    ->getUrl()
            );
            $result->addItem($image);
        }
        return $result;
    }
}
