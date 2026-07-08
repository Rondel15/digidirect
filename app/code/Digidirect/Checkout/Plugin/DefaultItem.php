<?php

namespace Digidirect\Checkout\Plugin;

use Magento\Framework\Pricing\PriceCurrencyInterface as CurrencyInterface;
use Magento\Quote\Model\Quote\Item;

class DefaultItem
{
    protected $currencyInterface;
    
    protected $productFactory;
    
    protected $_logger;

    public function __construct(
        CurrencyInterface $currencyInterface,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->currencyInterface = $currencyInterface;
        $this->productFactory = $productFactory;
        $this->_logger = $logger;
    }

    public function aroundGetItemData($subject, \Closure $proceed, Item $item)
    {
        $data = $proceed($item);
        $atts = [];
        $discount = 0; 
        $save = 0;
        
        $product_type = $item->getProduct()->getTypeId();
        $sku = $item->getProduct()->getSku();
        $qty = $item->getQty();
        //$this->_logger->debug($sku);
        
        if($product_type == 'configurable') {
            $product = $this->productFactory->create();
            $productdata = $product->loadByAttribute('sku', $sku);
            
            $currentPrice = $productdata->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
            $originalPrice = $productdata->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();
        } else {
            $currentPrice = $item->getProduct()->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
            $originalPrice = $item->getProduct()->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();
        }
        
        if ($currentPrice != $originalPrice) {
            $discount = round((($originalPrice - $item->getPrice()) / $originalPrice) * 100, 0);
            $save = ($originalPrice - $currentPrice) * $qty;
        }

        $atts = [
            "regular_price_value" => $originalPrice,
            "regular_price" => $this->currencyInterface->format($originalPrice, false, 2),
            "discount_percentage" => $discount, 
            "saved_amount" => $this->currencyInterface->format($save, false, 2),
            "saved_amount_value" => $save
        ];

        return array_merge($data, $atts);
    }
    
    public function getLoadProduct($id)
    {
        return $this->_productloader->create()->load($id);
    }
    
    /**
     * After plugin for getItemData
     * This is great for appending custom options (engraving, text, etc.)
     */
    public function afterGetItemData($subject, array $result, $item)
    {
        $product = $item->getProduct();
        $options = $product->getTypeInstance(true)->getOrderOptions($product);

        // ✅ Configurable product attributes (size, color, etc.)
        if (isset($options['attributes_info'])) {
            foreach ($options['attributes_info'] as $attr) {
                $result['options'][] = [
                    'label' => $attr['label'],
                    'value' => $attr['value'],
                ];
            }
        }

        // ✅ Customizable product options (text field, dropdown, etc.)
        if (isset($options['options']) && is_array($options['options'])) {
            foreach ($options['options'] as $opt) {
                $result['options'][] = [
                    'label' => $opt['label'],
                    'value' => $opt['value'],
                ];
            }
        }

        // 🔑 Fallback: get options from buyRequest (sometimes needed)
        $buyRequestOption = $item->getOptionByCode('info_buyRequest');
        if ($buyRequestOption) {
            $buyRequest = @unserialize($buyRequestOption->getValue());
            if (isset($buyRequest['options']) && is_array($buyRequest['options'])) {
                foreach ($buyRequest['options'] as $optionId => $optionValue) {
                    $result['options'][] = [
                        'label' => 'Option ' . $optionId, // fallback label
                        'value' => $optionValue,
                    ];
                }
            }
        }

        return $result;
    }
}
