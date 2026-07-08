<?php

namespace Digidirect\Vii\Api;

/**
 * Interface AbstractGiftCardEntityRepositoryInterface
 * @package Digidirect\Vii\Api
 */
interface AbstractGiftCardEntityRepositoryInterface
{
    /**
     * @param \Magento\Framework\DataObject $entityQuoteData
     * @return \Digidirect\AbstractGiftCard\Model\ResourceModel\AbstractGiftCardEntity
     */
    public function saveEntityQuoteData(\Magento\Framework\DataObject $entityQuoteData);
}
