<?php

namespace Digidirect\Catalog\Plugin\Pricing\Price;

class FinalPrice
{
    protected $customer;

    protected $logger;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->customer = $customerSession;
        $this->logger = $logger;
    }

    public function afterGetValue(\Magento\Catalog\Pricing\Price\FinalPrice $subject, $result)
    {
        $product = $subject->getProduct();
        $price = $product->getData('final_price');//$product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
        $wiserPrice = $product->getData('wiser_price');
        $basePrice = $product->getPrice();
        $discountWiserPrice = round($basePrice - $wiserPrice, 2);

        $isDigiPrint = $product->getData('is_digiprint');

        $sku = $product->getData('sku');

        $discount2 = []; //[122428,124928,130029,133828,135538,135790,137132,137431,139609,139908,142338,144625,144626,146718,146719,146818,146876,147859,148028,148817,149367,149381,152803,153589,153590,154948,154953,155161,155162,155166,155212,155520,155973];
        $discount5 = []; //[137376,137952,139517,141539,142768,149131,149132,149133,149950,153379,153380,153381,155158,155159,156474,156475,156476,156477,117561,117562,134146,117492,117494,122574,149496,149497];
        $discount10 = []; //[154979,154784,154782,154783,154785,151173,151169,141197,136339,148815,148728,155243,155244];
        $discount15 = []; //[133160,147857,130998,117586,153643,153642];

        $isDigiClub = 0;

        if ($this->customer->isLoggedIn()) {
            $customerGroupId = $this->customer->getCustomer()->getGroupId();
            if ($customerGroupId == 10) {
                $isDigiClub = 1;
            }
        }

        if ($product) {
            if (($wiserPrice > 0 && !empty($wiserPrice))) {
                if (($wiserPrice < $price)) {
                    if (!$isDigiPrint) {
                        if ((in_array($sku, $discount2)) && $isDigiClub) {
                            $wiserPrice = $wiserPrice - ($wiserPrice * 0.02);
                        } elseif ((in_array($sku, $discount5)) && $isDigiClub) {
                            $wiserPrice = $wiserPrice - ($wiserPrice * 0.05);
                        } elseif ((in_array($sku, $discount10)) && $isDigiClub) {
                            $wiserPrice = $wiserPrice - ($wiserPrice * 0.10);
                        } elseif ((in_array($sku, $discount15)) && $isDigiClub) {
                            $wiserPrice = $wiserPrice - ($wiserPrice * 0.15);
                        }
                        $result = $wiserPrice;
                    }
                } else {
                    $result = $price;
                }
            }

        } else {
            $result = $price;
        }

        return $result;

    }
}
