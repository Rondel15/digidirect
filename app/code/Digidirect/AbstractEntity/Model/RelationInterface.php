<?php
namespace Digidirect\AbstractEntity\Model;

/**
 * Interface RelationInterface
 * @package Digidirect\AbstractEntity\Model
 */
interface RelationInterface
{
    /**
     * @param int $entityId
     * @param array $data
     * @return mixed
     */
    public function processRelation($entityId, $data);
}
