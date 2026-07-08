<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Shopbybrand\Plugin\Block;

use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Block\Product\ListProduct as CatalogListProduct;
use Mageplaza\Shopbybrand\Block\Product\Logo;
use Mageplaza\Shopbybrand\Helper\Data;
use Mageplaza\Shopbybrand\Model\BrandFactory;

/**
 * Class ListProduct
 * @package Mageplaza\Shopbybrand\Plugin\Block
 */
class ListProduct extends Template
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var BrandFactory
     */
    protected $brandFactory;

    /**
     * @param Template\Context $context
     * @param ProductRepository $productRepository
     * @param BrandFactory $brandFactory
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        ProductRepository $productRepository,
        BrandFactory $brandFactory,
        Data $helper,
        array $data = []
    ) {
        $this->productRepository = $productRepository;
        $this->brandFactory      = $brandFactory;
        $this->helper            = $helper;

        parent::__construct($context, $data);
    }

    /**
     * @param CatalogListProduct $listProduct
     * @param callable $proceed
     * @param Product $product
     *
     * @return string
     * @throws LocalizedException
     */
    public function aroundGetProductPrice(
        CatalogListProduct $listProduct,
        callable $proceed,
        Product $product
    ) {
        if (!$this->helper->isEnabled() || empty($this->helper->getAttributeCode())) {
            return $proceed($product);
        }
        $brandObj = $this->getBrandObject($product->getId()) ? $this->getBrandObject($product->getId()) : '';

        if ($product->getTypeId() === 'configurable' && $brandObj === '') {
            $children = $product->getTypeInstance()->getUsedProducts($product);
            foreach ($children as $child) {
                if ($this->getBrandObject($child->getId())) {
                    $brandObj = $this->getBrandObject($child->getId());
                    break;
                }
            }
        }

        $info = $this->getLayout()
            ->createBlock(Logo::class)
            ->setTemplate('Mageplaza_Shopbybrand::product/logo.phtml')
            ->setIsShowList(1)
            ->setBrand($brandObj)
            ->toHtml();

        return $info . $proceed($product);
    }

    /**
     * @param int $productId
     *
     * @return DataObject|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getBrandObject($productId)
    {
        $currentProduct = $this->productRepository->getById($productId);
        if (!$currentProduct) {
            return null;
        }

        $optionId = $currentProduct->getData($this->helper->getAttributeCode());
        if (!$optionId) {
            return null;
        }

        return $this->brandFactory->create()->loadByOption($optionId);
    }
}
