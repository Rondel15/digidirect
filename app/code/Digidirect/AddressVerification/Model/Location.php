<?php
namespace Digidirect\AddressVerification\Model;

/**
 * Class Location
 * @package Digidirect\AddressVerification\Model
 */
class Location extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Location constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ResourceModel\Location $resource
     * @param ResourceModel\Location\CollectionFactory $resourceCollectionFactory
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Digidirect\AddressVerification\Model\ResourceModel\Location $resource,
        \Digidirect\AddressVerification\Model\ResourceModel\Location\CollectionFactory $resourceCollectionFactory
    ) {
        $resourceCollection = $resourceCollectionFactory->create();
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection
        );
    }
}
