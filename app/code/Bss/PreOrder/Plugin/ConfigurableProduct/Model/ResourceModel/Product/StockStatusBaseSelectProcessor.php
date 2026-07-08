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
namespace Bss\PreOrder\Plugin\ConfigurableProduct\Model\ResourceModel\Product;

use Bss\PreOrder\Helper\Data;
use Bss\PreOrder\Model\MinPriceConfigurable;
use Magento\Catalog\Model\ResourceModel\Product\BaseSelectProcessorInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status as StockStatusResource;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\NoSuchEntityException;

class StockStatusBaseSelectProcessor
{
    const BSS_C_P_E_I = "bss_c_p_e_i";

    /**
     * @var \Bss\PreOrder\Model\MinPriceConfigurable
     */
    protected $minPriceConfigurable;

    /**
     * @var StockStatusResource
     */
    private $stockStatusResource;

    /**
     * @var \Bss\PreOrder\Helper\Data
     */
    protected $helperData;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfig;

    /**
     * @param MinPriceConfigurable $minPriceConfigurable
     * @param StockConfigurationInterface $stockConfig
     * @param Data $helperData
     * @param StockStatusResource $stockStatusResource
     */
    public function __construct(
        \Bss\PreOrder\Model\MinPriceConfigurable $minPriceConfigurable,
        StockConfigurationInterface $stockConfig,
        \Bss\PreOrder\Helper\Data $helperData,
        StockStatusResource $stockStatusResource
    ) {
        $this->minPriceConfigurable = $minPriceConfigurable;
        $this->stockConfig = $stockConfig;
        $this->helperData = $helperData;
        $this->stockStatusResource = $stockStatusResource;
    }

    /**
     * Lowest price when config preorder
     * Improves the select with stock status sub query.
     *
     * @param \Magento\InventoryConfigurableProduct\Pricing\Price\LowestPriceOptionsProvider\StockStatusBaseSelectProcessor $subject
     * @param callable $proceed
     * @param Select $select
     * @return Select
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundProcess($subject, callable $proceed, Select $select)
    {
        if (!$this->helperData->isEnable()) {
            return $proceed($select);
        }

        if (!$this->stockConfig->isShowOutOfStock()) {
            return $select;
        }

        if ($this->stockConfig->isShowOutOfStock()) {
            $select->joinInner(
                ['stock' => $this->stockStatusResource->getMainTable()],
                sprintf(
                    'stock.product_id = %s.entity_id',
                    BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS
                ),
                []
            );
        }

        return $this->minPriceConfigurable->handlePreOrder($select, "stock_status");
    }
}
