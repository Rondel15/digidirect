<?php

namespace Digidirect\Vii\Model;

/**
 * Class AbstractGiftCardEntityRepository
 * @package Digidirect\Vii\Model
 */
class AbstractGiftCardEntityRepository implements \Digidirect\Vii\Api\AbstractGiftCardEntityRepositoryInterface
{
    /**
     * @var \Digidirect\AbstractGiftCard\Model\ResourceModel\AbstractGiftCardEntity
     */
    protected $resource;

    /**
     * AbstractGiftCardEntityRepository constructor.
     * @param ResourceModel\AbstractGiftCardEntity $resource
     */
    public function __construct(
        \Digidirect\Vii\Model\ResourceModel\AbstractGiftCardEntity $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * @param \Magento\Framework\DataObject $entityQuoteData
     * @return \Digidirect\AbstractGiftCard\Model\ResourceModel\AbstractGiftCardEntity
     */
    public function saveEntityQuoteData(\Magento\Framework\DataObject $entityQuoteData)
    {
        return $this->resource->saveEntityQuoteData($entityQuoteData);
    }

    /**
     * @param \Digidirect\AbstractGiftCard\Model\AbstractGiftCardEntity $entity
     * @param int $quoteId
     * @return \Magento\Framework\DataObject|null
     */
    public function getEntityQuoteData($entity, $quoteId)
    {
        return $this->resource->getEntityQuoteData($entity, $quoteId);
    }
}
