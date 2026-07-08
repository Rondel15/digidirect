<?php
namespace Digidirect\AI\Model\Lib\Entity\Import\Service;

interface PreparerInterface
{
    /**
     * @param array $entity
     * @return array
     */
    public function prepareEntity(array &$entity);
}
