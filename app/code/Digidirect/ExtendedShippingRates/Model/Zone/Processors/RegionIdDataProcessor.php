<?php

namespace Digidirect\ExtendedShippingRates\Model\Zone\Processors;

use Digidirect\ExtendedShippingRates\Api\Data\ZoneInterface;

/**
 * Class RegionIdDataProcessor
 * @package Digidirect\ExtendedShippingRates\Model\Zone\Processors
 */
class RegionIdDataProcessor extends AbstractDataProcessor
{
    const ATTRIBUTE = ZoneInterface::REGION_ID;
    const OPERATOR = '()';

    /**
     * @var \Magento\Directory\Model\ResourceModel\Region\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * RegionIdDataProcessor constructor.
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param array $data
     * @return array
     */
    public function prepareData(&$data)
    {
        $config = $this->getDefaultConfig();
        if (!empty($data[ZoneInterface::REGION_ID])) {
            $config['value'] = explode(',', $data[ZoneInterface::REGION_ID]);
        }

        return $config;
    }
}
