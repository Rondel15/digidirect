<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_PreOrder
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\PreOrder\Plugin\Helper;

use Bss\PreOrder\Helper\Data as PreOrderHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\ConfigurableProduct\Helper\Data as ConfigurableData;
use Magento\Framework\App\ObjectManager;

class Data extends ConfigurableData
{
    /**
     * @var PreOrderHelper
     */
    protected $preOrderHelper;

    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * @var UrlBuilder
     */
    protected $imageUrlBuilder;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param PreOrderHelper $preOrderHelper
     * @param ImageHelper $imageHelper
     * @param UrlBuilder|null $urlBuilder
     * @param ScopeConfigInterface|null $scopeConfig
     */
    public function __construct(
        PreOrderHelper $preOrderHelper,
        ImageHelper $imageHelper,
        UrlBuilder $urlBuilder = null,
        ?ScopeConfigInterface $scopeConfig = null
    ) {
        $this->preOrderHelper = $preOrderHelper;
        $this->imageUrlBuilder = $urlBuilder ?? ObjectManager::getInstance()->get(UrlBuilder::class);
        $this->scopeConfig = $scopeConfig ?? ObjectManager::getInstance()->get(ScopeConfigInterface::class);
        parent::__construct($imageHelper);
    }

    /**
     * Get Options for Configurable Product Options
     *
     * @param Product $currentProduct
     * @param array $allowedProducts
     * @return array
     */
    public function getOptions($currentProduct, $allowedProducts)
    {
        $options = [];
        $allowAttributes = $this->getAllowAttributes($currentProduct);

        foreach ($allowedProducts as $product) {
            $productId = $product->getId();
            foreach ($allowAttributes as $attribute) {
                $productAttribute = $attribute->getProductAttribute();
                $productAttributeId = $productAttribute->getId();
                $attributeValue = $product->getData($productAttribute->getAttributeCode());
                if ($this->canDisplayShowOutOfStockStatus()) {
                    if ($product->isSalable()) {
                        $options['salable'][$productAttributeId][$attributeValue][] = $productId;
                        $options[$productAttributeId][$attributeValue][] = $productId;
                    }
                } else {
                    if ($product->isSalable()) {
                        $options[$productAttributeId][$attributeValue][] = $productId;
                    }
                }
                $options['index'][$productId][$productAttributeId] = $attributeValue;
            }
        }
        $options['canDisplayShowOutOfStockStatus'] = $this->canDisplayShowOutOfStockStatus();
        return $options;
    }

    /**
     * Returns if display out of stock status set or not in catalog inventory
     *
     * @return bool
     */
    private function canDisplayShowOutOfStockStatus(): bool
    {
        if ($this->preOrderHelper->isDisplayOutOfStockProduct()) {
            return false;
        }
        return (bool) $this->scopeConfig->getValue('cataloginventory/options/show_out_of_stock');
    }
}
