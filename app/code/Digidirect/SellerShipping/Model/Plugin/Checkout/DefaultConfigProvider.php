<?php

namespace Digidirect\SellerShipping\Model\Plugin\Checkout;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class DefaultConfigProvider
{
    
    protected $helperData;
    
    protected $logger;
    
    
    public function __construct(
        \Digidirect\SellerShipping\Helper\Data $helperData,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->helperData = $helperData;
        $this->logger = $logger;
    }

    /**
     * AfterGetConfig
     *
     * @param \Magento\Checkout\Model\DefaultConfigProvider $subject
     * @param [] $result
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    
    public function afterGetConfig(
        \Magento\Checkout\Model\DefaultConfigProvider $subject,
        $result
    ) {
        //$this->logger->info('DefaultConfigProvider getSellersShipping(): ' . $this->helperData->getSellerShipping());
        $result['quoteData']['has_marketplacer_seller'] = $this->helperData->hasMarketplacerSeller();
        $result['quoteData']['marketplacer_sellers'] = $this->helperData->getSellers();
        
        $items = $result['totalsData']['items'];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        for($i=0; $i < count($items); $i++){
            $quoteId = $items[$i]['item_id'];
            $quote = $objectManager->create('\Magento\Quote\Model\Quote\Item')->load($quoteId);
            $productId = $quote->getProductId();
            $product = $objectManager->create('\Magento\Catalog\Model\Product')->load($productId);
            $productSeller = $product->getResource()->getAttribute('marketplacer_seller')->getFrontend()->getValue($product);       
            $items[$i]['marketplacer_seller'] = $productSeller;
        }
        $result['totalsData']['items'] = $items;
        
        return $result;
    }

}
