<?php
/**
 * @author      WebPanda
 * @package     WebPanda_ReviewNotification
 * @copyright   Copyright (c) WebPanda (https://webpanda-solutions.com/)
 * @license     https://webpanda-solutions.com/license-agreement
 */

namespace WebPanda\SalesProductImage\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Helper\ImageFactory as HelperFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\UrlInterface;

/**
 * Class Data
 * @package WebPanda\SalesProductImage\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var HelperFactory
     */
    protected $imageHelperFactory;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Data constructor.
     * @param Context $context
     * @param HelperFactory $imageHelperFactory
     * @param ProductRepository $productRepository
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        Context $context,
        HelperFactory $imageHelperFactory,
        ProductRepository $productRepository,
        UrlInterface $urlBuilder
    ) {
        $this->imageHelperFactory = $imageHelperFactory;
        $this->productRepository = $productRepository;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context);
    }

    /**
     * Retrieve product image
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $imageId
     * @param array $attributes
     * @return string
     */
    public function getImageUrl($product, $imageId, $attributes = [])
    {
        $helper = $this->imageHelperFactory->create();
        $helper->init($product, $imageId, $attributes);

        return $helper->getUrl();
    }

    /**
     * @param $item
     * @param $area
     * @param $position
     * @return string
     */
    public function renderImage($item, $area = 'front', $position = 'left')
    {
        if (!is_object($item)) {
            $productId = $item;
            $storeId = 0;
        } else {
            $productId = $item->getProductId();
            $storeId = $item->getStoreId();
        }
        $productExists = true;
        try {
            $product = $this->productRepository->getById($productId, false, $storeId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $productExists = false;
        }
        // todo set sample image from settings
        if (!$productExists || !$product->getId()) {
            return '';
        }

        // todo get the imageId from settings
        // product_thumbnail_image
        // product_small_image

        if ($area == 'front') {
            $productUrl = $product->getProductUrl();
        } else {
            $productUrl = $this->urlBuilder->getUrl(
                'catalog/product/edit',
                ['id' => $productId]
            );
        }

        switch ($position) {
            case 'center':
                $result = '<a href="' . $productUrl . '" target="_blank"><img src="' . $this->getImageUrl($product, 'product_thumbnail_image') . '" alt="' . __('Product Image') . '" /></a>';
                break;
            case 'none':
                $result = '<img src="' . $this->getImageUrl($product, 'product_thumbnail_image') . '" alt="' . __('Product Image') . '" />';
                break;
            default:
                $result = '<a href="' . $productUrl . '" target="_blank"><img src="' . $this->getImageUrl($product, 'product_thumbnail_image') . '" alt="' . __('Product Image') . '"/></a>';
                break;
        }

        return $result;
    }
}
