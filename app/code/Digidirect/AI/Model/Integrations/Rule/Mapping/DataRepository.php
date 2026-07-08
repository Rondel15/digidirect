<?php
namespace Digidirect\AI\Model\Integrations\Rule\Mapping;

use Digidirect\AI\Model\ResourceModel\Integrations\Rule\Mapping as MappingResource;
use Digidirect\AI\Model\ResourceModel\Integrations\Rule\Mapping\Collection as MappingResourceCollection;
use Digidirect\AI\Api\Data\MapperDataInterface as MappingData;
use Digidirect\AI\Model\Integrations\Rule\Mapping\DataFactory;
use Magento\Framework\Exception\CouldNotSaveException;

class DataRepository implements \Digidirect\AI\Api\MapperDataRepositoryInterface
{
    /**
     * @var \Digidirect\AI\Model\ResourceModel\Integrations\Rule\Mapping
     */
    protected $_resource;

    /**
     * @var \Digidirect\AI\Model\ResourceModel\Integrations\Rule\Mapping\Collection
     */
    protected $_resourceCollection;

    /**
     * @var \Digidirect\AI\Model\Integrations\Rule\Mapping\DataFactory
     */
    protected $_dataFactory;

    /**
     * @param MappingResource $resource
     * @param  MappingResourceCollection $resourceCollection
     * @param DataFactory $dataFactory
     */
    public function __construct(
        MappingResource $resource,
        MappingResourceCollection $resourceCollection,
        DataFactory $dataFactory
    ) {
        $this->_resource = $resource;
        $this->_resourceCollection = $resourceCollection;
        $this->_dataFactory = $dataFactory;
    }

    /**
     * Save mapping data
     *
     * @param MappingData $mappingData
     * @return MappingData
     * @throws CouldNotSaveException
     */
    public function save(MappingData $mappingData)
    {
        try {
            $this->_resource->save($mappingData);
        } catch (\Throwable $exception) {
            throw new CouldNotSaveException(__('We can\'t save the mapping data.'), $exception);
        }
        return $mappingData;
    }

    /**
     * @param int $entityId
     * @param string|null $mappingCode
     * @return $this
     */
    public function load($entityId, $mappingCode = null)
    {
        if ($mappingCode === null) {
            $mappingData = $this->_dataFactory->create();
            $this->_resource->load($mappingData, $entityId, MappingData::ENTITY_ID);
            return $mappingData;
        }

        $this->_resourceCollection
            ->addFieldToFilter(MappingData::ENTITY_ID, $entityId)
            ->addFieldToFilter(MappingData::MAPPER_CODE, $mappingCode);

        return $this->_resourceCollection->getFirstItem();
    }

    /**
     * Load mapping data by given mapping code
     *
     * @param string $mappingCode
     * @return MappingData
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function loadByMappingCode($mappingCode)
    {
        return $this->_resourceCollection->addFieldToFilter(MappingData::MAPPER_CODE, $mappingCode);
    }
}
