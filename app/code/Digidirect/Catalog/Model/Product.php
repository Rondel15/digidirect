<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Digidirect\Catalog\Model;

/**
 * Catalog product model
 *
 * @api
 * @method Product setHasError(bool $value)
 * @method null|bool getHasError()
 * @method array getAssociatedProductIds()
 * @method Product setNewVariationsAttributeSetId(int $value)
 * @method int getNewVariationsAttributeSetId()
 * @method int getPriceType()
 * @method string getUrlKey()
 * @method Product setUrlKey(string $urlKey)
 * @method Product setRequestPath(string $requestPath)
 * @method Product setWebsiteIds(array $ids)
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Product extends \Magento\Catalog\Model\Product
{
    public function getPoints()
    {
        if ($this->_calculatePrice || !$this->getData(self::PRICE)) {
            //API HERE
            $price = $this->getPriceModel()->getPrice($this);
            $initial_qff_points = number_format($price * 2);
            
            return $initial_qff_points;
        } else {
            return $this->getData(self::PRICE);
        }
    }
    
    /**
     * Get product qff bonus points through type instance
     *
     * @return float
     */
    public function getBonusPoints()
    {
        if ($this->_calculatePrice || !$this->getData(self::PRICE)) {
            //API HERE
            $price = $this->getPriceModel()->getPrice($this);
            $initial_qff_bonus_points = number_format($price * 2);
            
            return $initial_qff_bonus_points;
        } else {
            return $this->getData(self::PRICE);
        }
    }
}
