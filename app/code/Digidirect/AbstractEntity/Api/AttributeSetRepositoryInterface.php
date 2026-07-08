<?php
namespace Digidirect\AbstractEntity\Api;

interface AttributeSetRepositoryInterface extends \Magento\Eav\Api\AttributeSetRepositoryInterface
{
    /**
     * @param int $entityId
     * @param array $data
     * @return mixed
     */
    public function processRelations($entityId, $data);
}
