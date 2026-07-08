<?php

namespace Digidirect\Blog\Model;

use Magento\Framework\DataObject;

/**
 * @api
 * @since 1.3.3
 */
class IdentitiesGenerator
{
    /**
     * @param DataObject $dataObject
     * @param $cachePrefix
     * @return array
     */
    public function getIdentities(DataObject $dataObject, $cachePrefix): array
    {
        $identities = [
            $this->makeIdentity($dataObject->getId(), $cachePrefix)
            //$cachePrefix . '_' . $dataObject->getId(),
        ];
        if (!$dataObject->getId() || $dataObject->hasDataChanges() || $dataObject->isDeleted()) {
            $identities[] = $cachePrefix;
        }
        return $identities;
    }

    /**
     * @since 1.3.5
     * @param int|string $identityId
     * @param string $cachePrefix
     * @return string
     */
    public function makeIdentity($identityId, $cachePrefix)
    {
        return $cachePrefix . '_' . $identityId;
    }
}
