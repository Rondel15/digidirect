<?php

namespace Digidirect\ProductOverlay\Api\Data;

/**
 * Product overlay interface.
 *
 * @api
 */
interface OverlayInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const OVERLAY_ID = 'overlay_id';
    const POS = 'pos';
    const IS_SINGLE = 'is_single';
    const NAME = 'name';
    const STATUS = 'status';
    const STORES = 'stores';
    const PROD_TXT = 'prod_txt';
    const PROD_IMG = 'prod_img';
    const PROD_IMAGE_SIZE = 'prod_image_size';
    const PROD_POS = 'prod_pos';
    const CAT_TXT = 'cat_txt';
    const CAT_IMG = 'cat_img';
    const CAT_POS = 'cat_pos';
    const CAT_IMAGE_SIZE = 'cat_image_size';
    const IS_NEW = 'is_new';
    const IS_SALE = 'is_sale';
    const SPECIAL_PRICE_ONLY = 'special_price_only';
    const STOCK_LESS = 'stock_less';
    const STOCK_MORE = 'stock_more';
    const STOCK_STATUS = 'stock_status';
    const FROM_DATE = 'from_date';
    const TO_DATE = 'to_date';
    const DATE_RANGE_ENABLED = 'date_range_enabled';
    const FROM_PRICE = 'from_price';
    const TO_PRICE = 'to_price';
    const BY_PRICE = 'by_price';
    const PRICE_RANGE_ENABLED = 'price_range_enabled';
    const CUSTOMER_GROUP_IDS = 'customer_group_ids';
    const COND_SERIALIZE = 'cond_serialize';
    const CUSTOMER_GROUP_ENABLED = 'customer_group_enabled';
    const USE_FOR_PARENT = 'use_for_parent';
    const PRIVATE_SALES_ENABLED = 'private_sales_enabled';
    const CATALOG_PRICE_RULES_IDS = 'catalog_price_rules_ids';
    const STOCK_FROM = 'stock_from';
    const STOCK_TO = 'stock_to';
    const PROD_STOCK_LABEL = 'prod_stock_label';

    /**#@-*/

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get position
     *
     * @return string|null
     */
    public function getPos();

    /**
     * Check is overlay single
     *
     * @return bool|null
     */
    public function getIsSingle();

    /**
     * Get name
     *
     * @return string|null
     */
    public function getName();

    /**
     * Get status code
     *
     * @return int|null
     */
    public function getStatus();

    /**
     * Get stores
     *
     * @return string|null
     */
    public function getStores();

    /**
     * @return bool
     */
    public function getPrivateSalesEnabled();

    /**
     * @return integer[]
     */
    public function getCatalogPriceRulesIds();

    /**
     * Get product text
     *
     * @return string|null
     */
    public function getProdTxt();

    /**
     * Get product image
     *
     * @return string|null
     */
    public function getProdImg();

    /**
     * Get product image size
     *
     * @return string|null
     */
    public function getProdImageSize();

    /**
     * Get product position
     *
     * @return string|null
     */
    public function getProdPos();

    /**
     * Get category text
     *
     * @return string|null
     */
    public function getCatTxt();

    /**
     * Get category image
     *
     * @return string|null
     */
    public function getCatImg();

    /**
     * Get category image size
     *
     * @return string|null
     */
    public function getCatImageSize();

    /**
     * Get category position
     *
     * @return string|null
     */
    public function getCatPos();

    /**
     * Get category position
     *
     * @return string|null
     */
    public function getIsNew();

    /**
     * Get is sale
     *
     * @return string|null
     */
    public function getIsSale();

    /**
     * Get special price only
     *
     * @return string|null
     */
    public function getSpecialPriceOnly();

    /**
     * Get stock less
     *
     * @return string|null
     */
    public function getStockLess();

    /**
     * Get stock more
     *
     * @return string|null
     */
    public function getStockMore();

    /**
     * Get stock status
     *
     * @return string|null
     */
    public function getStockStatus();

    /**
     * Get from date
     *
     * @return string|null
     */
    public function getFromDate();

    /**
     * Get to date
     *
     * @return string|null
     */
    public function getToDate();

    /**
     * Get date range enabled
     *
     * @return string|null
     */
    public function getDateRangeEnabled();

    /**
     * Get from price
     *
     * @return string|null
     */
    public function getFromPrice();

    /**
     * Get to price
     *
     * @return string|null
     */
    public function getToPrice();

    /**
     * Get by price
     *
     * @return string|null
     */
    public function getByPrice();

    /**
     * Get price range enabled
     *
     * @return string|null
     */
    public function getPriceRangeEnabled();

    /**
     * Get customer group ids
     *
     * @return string|null
     */
    public function getCustomerGroupIds();

    /**
     * Get cond serialize
     *
     * @return string|null
     */
    public function getCondSerialize();

    /**
     * Get customer group enabled
     *
     * @return string|null
     */
    public function getCustomerGroupEnabled();

    /**
     * Get use for parent
     *
     * @return string|null
     */
    public function getUseForParent();

    /**
     * Set ID
     *
     * @param int $id
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setId($id);

    /**
     * Set Pos
     *
     * @param string $pos
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setPos($pos);

    /**
     * Set is single
     *
     * @param bool $isSingle
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setIsSingle($isSingle);

    /**
     * Set name
     *
     * @param string $name
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setName($name);

    /**
     * Set status
     *
     * @param string $status
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setStatus($status);

    /**
     * Set stores
     *
     * @param string $stores
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setStores($stores);

    /**
     * Set prod txt
     *
     * @param string $prodTxt
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setProdTxt($prodTxt);

    /**
     * Set prod img
     *
     * @param string $prodImg
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setProdImg($prodImg);

    /**
     * Set prod image size
     *
     * @param string $prodImageSize
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setProdImageSize($prodImageSize);

    /**
     * Set prod pos
     *
     * @param string $prodPos
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setProdPos($prodPos);

    /**
     * Set cat txt
     *
     * @param string $catTxt
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setCatTxt($catTxt);

    /**
     * Set cat img
     *
     * @param string $catImg
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setCatImg($catImg);

    /**
     * Set cat image size
     *
     * @param string $catImageSize
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setCatImageSize($catImageSize);

    /**
     * Set cat pos
     *
     * @param string $catPos
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setCatPos($catPos);

    /**
     * Set is new
     *
     * @param string $isNew
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setIsNew($isNew);

    /**
     * Set is sale
     *
     * @param string $isSale
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setIsSale($isSale);

    /**
     * Set special price only
     *
     * @param string $specialPriceOnly
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setSpecialPriceOnly($specialPriceOnly);

    /**
     * Set stock less
     *
     * @param string $stockLess
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setStockLess($stockLess);

    /**
     * Set stock more
     *
     * @param string $stockMore
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setStockMore($stockMore);

    /**
     * Set stock status
     *
     * @param string $stockStatus
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setStockStatus($stockStatus);

    /**
     * Set from date
     *
     * @param string $fromDate
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setFromDate($fromDate);

    /**
     * Set to date
     *
     * @param string $toDate
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setToDate($toDate);

    /**
     * Set date range enabled
     *
     * @param string $dateRangeEnabled
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setDateRangeEnabled($dateRangeEnabled);

    /**
     * Set from price
     *
     * @param string $fromPrice
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setFromPrice($fromPrice);

    /**
     * Set to price
     *
     * @param string $toPrice
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setToPrice($toPrice);

    /**
     * Set by price
     *
     * @param string $byPrice
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setByPrice($byPrice);

    /**
     * Set price range enabled
     *
     * @param string $priceRangeEnabled
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setPriceRangeEnabled($priceRangeEnabled);

    /**
     * Set customer group ids
     *
     * @param string $customerGroupIds
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setCustomerGroupIds($customerGroupIds);

    /**
     * Set cond serialize
     *
     * @param string $condSerialize
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setCondSerialize($condSerialize);

    /**
     * Set customer group enabled
     *
     * @param string $customerGroupEnabled
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setCustomerGroupEnabled($customerGroupEnabled);

    /**
     * Set use for parent
     *
     * @param bool $useForParent
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setUseForParent($useForParent);

    /**
     * @return mixed
     */
    public function getStockFrom();

    /**
     * @return mixed
     */
    public function getStockTo();

    /**
     * @param string $from
     * @return mixed
     */
    public function setStockFrom($from);

    /**
     * @param string $stockTo
     * @return mixed
     */
    public function setStockTo($stockTo);
}
