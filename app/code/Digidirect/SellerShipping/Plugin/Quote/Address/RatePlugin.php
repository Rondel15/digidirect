<?php

namespace Digidirect\SellerShipping\Plugin\Quote\Address;

class RatePlugin
{
    protected $helperData;
    
    protected $logger;
    
    
    public function __construct(
        \Digidirect\SellerShipping\Helper\Data $helperData,
        \Psr\Log\LoggerInterface $logger
    ){
        $this->helperData = $helperData;
        $this->logger = $logger;
    }
    
    public function afterImportShippingRate(\Magento\Quote\Model\Quote\Address\Rate $subject, $result, $rate)
    {
        //$this->logger->info('afterImportShippingRate');
        if ($rate instanceof \Magento\Quote\Model\Quote\Address\RateResult\Method) {
            $this->logger->info('getCode: ' . $result->getCode());
            $this->logger->info('getCarrier: ' . $result->getCarrier());
            $this->logger->info('getCarrierTitle: ' . $result->getCarrierTitle());
            $this->logger->info('getMethod: ' . $result->getMethod());
            $this->logger->info('getMethodTitle: ' . $result->getMethodTitle());
            if($result->getCode() == 'standard_standard') {
                $result->setPrice($this->helperData->getDigiShipping());
                //$this->logger->info('standard afterImportShippingRate: ' . $this->helperData->getDigiShipping());
            }
            $result->setPrice($result->getPrice() + $this->helperData->getSellerShipping());
        }
        return $result;
    }

}
