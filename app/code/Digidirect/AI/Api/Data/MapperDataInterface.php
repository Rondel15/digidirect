<?php
namespace Digidirect\AI\Api\Data;

interface MapperDataInterface
{
    const ENTITY_ID = 'entity_id';
    const MAPPER_CODE = 'mapper_code';
    const DATA = 'data';

    /**
     * Get entity id
     *
     * @return int|null
     */
    public function getEntityId();

    /**
     * Set entity id
     *
     * @param int $entityId
     * @return MapperDataInterface
     */
    public function setEntityId($entityId);

    /**
     * Get mapper code
     *
     * @return string|null
     */
    public function getCode();

    /**
     * Set mapper code
     *
     * @param string $code
     * @return MapperDataInterface
     */
    public function setCode($code);

    /**
     * Get data
     *
     * @return string|null
     */
    public function getMapperData();

    /**
     * Set data
     *
     * @param array $data
     * @return MapperDataInterface
     */
    public function setMapperData($data = []);
}
