<?php

namespace Digidirect\ProductOverlay\Model;

use Digidirect\ProductOverlay\Api\Data\OverlayInterface;
use Magento\Catalog\Model\Product;

/**
 * Class Overlays
 * @package Digidirect\ProductOverlay\Model
 * @method \Magento\Catalog\Model\Product getProduct()
 */
class Overlays extends AbstractOverlays
{
    const TEXT_ATTRIBUTE_PRICE = 'PRICE';
    const TEXT_ATTRIBUTE_SPECIAL_PRICE = 'SPECIAL_PRICE';
    const TEXT_ATTRIBUTE_FINAL_PRICE = 'FINAL_PRICE';
    const TEXT_ATTRIBUTE_FINAL_PRICE_INCL_TAX = 'FINAL_PRICE_INCL_TAX';
    const TEXT_ATTRIBUTE_STARTINGFROM_PRICE = 'STARTINGFROM_PRICE';
    const TEXT_ATTRIBUTE_STARTINGTO_PRICE = 'STARTINGTO_PRICE';
    const TEXT_ATTRIBUTE_SAVE_AMOUNT = 'SAVE_AMOUNT';
    const TEXT_ATTRIBUTE_SAVE_PERCENT = 'SAVE_PERCENT';
    const TEXT_ATTRIBUTE_BR = 'BR';
    const TEXT_ATTRIBUTE_SKU = 'SKU';
    const TEXT_ATTRIBUTE_NEW_FOR = 'NEW_FOR';
    const TEXT_ATTRIBUTE_DEFAULT = 'ATTR:';

    const STOCK_VARIABLE_QTY = 'STOCK';

    const CURRENT_OVERLAY_REGISTRY = 'current_digidirect_productoverlay';

    const POSITION_MODE_CATEGORY = 'cat';
    const POSITION_MODE_PRODUCT = 'prod';

    /**
     * @var array
     */
    protected $_horisontalPositions = ['left', 'center', 'right'];

    /**
     * @var array
     */
    protected $_verticalPositions = ['top', 'middle', 'bottom'];

    /**
     * Combine all variation of overlay position.
     *
     * @param bool $asText
     * @return array
     */
    public function getAvailablePositions($asText = true)
    {
        $a = [];
        foreach ($this->_verticalPositions as $first) {
            foreach ($this->_horisontalPositions as $second) {
                $a[] = $asText ?
                    __(ucwords($first . ' ' . $second))
                    :
                    $first . '-' . $second;
            }
        }

        return $a;
    }

    /**
     * Get position value of overlay
     * @return string
     */
    public function getCssClass()
    {
        $all = $this->getAvailablePositions(false);

        switch ($this->getMode()) {
            case self::POSITION_MODE_CATEGORY:
                return $all[$this->getCatPos()];
            default:
                return $all[$this->getProdPos()];
        }
    }

    /**
     * Get overlay text with replacing data
     * @return string
     */
    public function getText()
    {
        $txt = $this->getValue('txt');

        preg_match_all('/{([a-zA-Z:\_0-9]+)}/', $txt, $vars);
        if (!$vars[1]) {
            return $txt;
        }
        $vars = $vars[1];

        foreach ($vars as $var) {
            $value = $this->_getTextValueByLabel($var);
            $txt = str_replace('{' . $var . '}', $value, $txt);
        }

        return $txt;
    }

    /**
     * @return string
     */
    public function getStockLabel()
    {
        $label = $this->getValue('stock_label');
        if(!empty($label))
        {
            preg_match_all('/{([a-zA-Z:\_0-9]+)}/', $label, $vars);
        }

            if (isset($vars[1])) {
                $vars = $vars[1];
                foreach ($vars as $var) {
                    $value = $this->_processStockVariable($var);
                    $label = str_replace('{' . $var . '}', $value, $label);
                }
            }
        return (string)$label;
    }

    /**
     * @param string $var
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _processStockVariable($var)
    {
        /** @var Product $product */
        $product = $this->getProduct();
        switch ($var) {
            case self::STOCK_VARIABLE_QTY:
                /** Magento\CatalogInventory\Model\Stock\Item $stock */
                $stock = $this->_stockRegistry->getStockItem($product->getId());
                $value = $stock->getQty();
                break;
            default:
                $value = '';
        }

        return $value;
    }

    /**
     * Strip tag from price and convert it to store format
     *
     * @param string $price
     * @return string
     */
    protected function _convertPrice($price)
    {
        $store = $this->_storeManager->getStore();
        return strip_tags($this->_priceCurrency->convertAndFormat($price, $store));
    }

    /**
     * @param string $label
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _getTextValueByLabel($label)
    {
        /** @var Product $product */
        $product = $this->getProduct();
        switch ($label) {
            case self::TEXT_ATTRIBUTE_PRICE:
                $price = $this->_loadPrices();
                $value = $this->_convertPrice($price['price']);
                break;
            case self::TEXT_ATTRIBUTE_SPECIAL_PRICE:
                $price = $this->_loadPrices();
                $value = $this->_convertPrice($price['special_price']);
                break;
            case self::TEXT_ATTRIBUTE_FINAL_PRICE:
                $value = $this->_convertPrice(
                    $this->_catalogData->getTaxPrice($product, $product->getFinalPrice(), false)
                );
                break;
            case self::TEXT_ATTRIBUTE_FINAL_PRICE_INCL_TAX:
                $value = $this->_convertPrice(
                    $this->_catalogData->getTaxPrice($product, $product->getFinalPrice(), true)
                );
                break;
            case self::TEXT_ATTRIBUTE_STARTINGFROM_PRICE:
                $value = $this->_convertPrice($this->_getMinimalPrice($product));
                break;
            case self::TEXT_ATTRIBUTE_STARTINGTO_PRICE:
                $value = $this->_convertPrice($this->_getMaximalPrice($product));
                break;
            case self::TEXT_ATTRIBUTE_SAVE_AMOUNT:
                $price = $this->_loadPrices();
                $value = $this->_convertPrice($price['price'] - $price['special_price']);
                break;
            case self::TEXT_ATTRIBUTE_SAVE_PERCENT:
                $value = 0;
                $price = $this->_loadPrices();
                if ($price['price']) {
                    $value = $price['price'] - $price['special_price'];
                    $value = $this->_calcOnSailPercent($value, $price['price']);
                }
                break;

            case self::TEXT_ATTRIBUTE_BR:
                $value = '<br/>';
                break;

            case self::TEXT_ATTRIBUTE_SKU:
                $value = $product->getSku();
                break;

            case self::TEXT_ATTRIBUTE_NEW_FOR:
                $createdAt = strtotime($product->getCreatedAt());
                $value = max(1, floor((time() - $createdAt) / 86400));
                break;

            default:
                $value = $this->_getDefaultAttributeValue($product);
        }

        return $value;
    }

    /**
     * @param string $label
     * @return mixed|string
     */
    protected function _getDefaultAttributeValue($label)
    {
        /** @var Product $product */
        $product = $this->getProduct();
        $value = '';
        $str = self::TEXT_ATTRIBUTE_DEFAULT;

        if (substr($label, 0, strlen($str)) == $str) {
            $code = trim(substr($label, strlen($str)));

            $decimal = null;
            if (false !== strpos($code, ':')) {
                $temp = explode(':', $code);
                $code = $temp[0];
                $decimal = $temp[1];
            }

            $value = $product->getData($code);
            if (is_numeric($value) && $product->getData($code . '_value')) {
                $value = $product->getData($code . '_value');
            }

            if (!(null === $decimal)
                && false !== strpos($value, '.')
            ) {
                $temp = explode('.', $value);
                $value = $temp[0] . '.' . substr($temp[1], 0, $decimal);
            }

            if (preg_match("/([0-9]{4})-([0-9]{2})-([0-9]{2})/", $value)) {
                $value = $this->_date->formatDateTime(
                    new \DateTime($value),
                    \IntlDateFormatter::MEDIUM,
                    \IntlDateFormatter::NONE
                );
            }
        }

        return $value;
    }

    /**
     * @return $this
     */
    public function prepareDateRangeValues()
    {
        if (!(int)$this->getFromDate()) {
            $this->setFromDate($this->_date->date());
        }
        if (!(int)$this->getToDate()) {
            $this->setToDate($this->_date->date());
        }
        return $this;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validateTimes()
    {
        /*$expression = '/[0-9]|:/';
        if ($symbols = preg_replace($expression, '', $this->getFromTime())) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Unacceptable symbol(s) "%1" in "From Time" field.', $symbols)
            );
        }
        if ($symbols = preg_replace($expression, '', $this->getToTime())) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Unacceptable symbol(s) "%1" in "To Time" field.', $symbols)
            );
        }*/

        return true;
    }

    /**
     * @return bool
     */
    public function validateDates()
    {
        if ($this->getDateRangeEnabled() && !empty($this->getFromDate()) && !empty($this->getToDate())) {
            return $this->getFromDate() <= $this->getToDate();
        }
        return true;
    }

    /**
     * @return bool
     */
    public function validatePrices()
    {
        if ($this->getPriceRangeEnabled() && !empty($this->getFromPrice()) && $this->getToPrice() !== null) {
            return $this->getFromPrice() <= $this->getToPrice();
        }
        return true;
    }

    /**
     * @return $this
     */
    public function beforeSave()
    {
        $this->convertFromArrayToString();
        parent::beforeSave();
        return $this;
    }

    /**
     * @return void
     */
    protected function convertFromArrayToString()
    {
        $parametersToConvert = $this->config['parameters_to_convert'] ?? [];
        foreach ($parametersToConvert as $parameter) {
            $arrayParameter = $this->getData($parameter);
            if (is_array($arrayParameter)) {
                $arrayParameter = implode(',', $arrayParameter);
            }
            $this->setData($parameter, $arrayParameter);
        }
    }

    /**
     * @return void
     */
    protected function convertFromStringToArray()
    {
        $parametersToConvert = $this->config['parameters_to_convert'] ?? [];
        foreach ($parametersToConvert as $parameter) {
            $arrayParameter = $this->getData($parameter);
            if (!is_array($arrayParameter)) {
                if(!empty($arrayParameter))
                {
                    $arrayParameter = explode(',', $arrayParameter);
                    $arrayParameter = array_filter($arrayParameter);
                }

            }
            $this->setData($parameter, $arrayParameter);
        }
    }

    /**
     * @return $this
     */
    public function afterLoad()
    {
        $this->convertFromStringToArray();
        parent::afterLoad();
        return $this;
    }

    /**
     * Template method to return validate rules for the entity
     *
     * @return \Zend_Validate_Interface|null
     */
    protected function _getValidationRulesBeforeSave()
    {
        return $this->config['validator'] ?? null;
    }
}
