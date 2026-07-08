<?php
namespace Digidirect\AI\Api;

use Digidirect\AI\Api\Data\MapperDataInterface;

/**
 * Interface QueueRepositoryInterface
 *
 * @package Digidirect\AI\Api
 */
interface MapperDataRepositoryInterface
{
    /**
     * Save mapping data
     *
     * @param MapperDataInterface $mappingData
     * @return MapperDataInterface
     */
    public function save(MapperDataInterface $mappingData);

    /**
     * @param int $entityId
     * @param string|null $mappingCode
     * @return MapperDataInterface
     */
    public function load($entityId, $mappingCode = null);

    /**
     * @param string $mappingCode
     * @return MapperDataInterface
     */
    public function loadByMappingCode($mappingCode);
}
