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
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\PreOrder\Model\Provider;

use Bss\PreOrder\Helper\Data as PreOrderHelper;
use Bss\PreOrder\Helper\ProductData;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\SessionFactory;

/**
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class PreOrderProducts implements ConfigProviderInterface
{
    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var PreOrderHelper
     */
    protected $preOrderHelper;

    /**
     * @var SessionFactory
     */
    protected $sessionFactory;

    /**
     * @var ProductData
     */
    protected $productData;

    /**
     * PreOrderProducts constructor.
     * @param ProductCollectionFactory $productCollectionFactory
     * @param PreOrderHelper $preOrderHelper
     * @param SessionFactory $sessionFactory
     * @param ProductData $productData
     */
    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        PreOrderHelper $preOrderHelper,
        SessionFactory $sessionFactory,
        \Bss\PreOrder\Helper\ProductData $productData
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->preOrderHelper = $preOrderHelper;
        $this->sessionFactory = $sessionFactory;
        $this->productData = $productData;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getConfig()
    {
        return [
            'pre_order_enable' => $this->preOrderHelper->isEnable(),
            'pre_order_ids' => $this->getJsonPreOrderProducts(),
            'pre_order_note' => $this->preOrderHelper->getNote() ?: __('Pre-Ordered Product')
        ];
    }

    /**
     * Get json pre-order products
     *
     * @return false|string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getJsonPreOrderProducts()
    {
        $products = $this->productData->getPreOrderProducts();
        $dataAfter = [];

        if ($products) {
            $quoteItems = $this->sessionFactory->create()->getQuote()->getItems();
            /** @var \Magento\Quote\Api\Data\CartItemInterface $item */
            foreach ($quoteItems as $item) {
                foreach ($products as $product) {
                    if ($product->getSku() == $item->getSku() && $this->preOrderHelper->checkPreOrderAvailability($product, $item)) {
                        $dataAfter[$item->getItemId()] = $product->getSku();
                    }
                }
            }
        }
        return $this->preOrderHelper->serializeClass()->serialize($dataAfter);
    }

}
