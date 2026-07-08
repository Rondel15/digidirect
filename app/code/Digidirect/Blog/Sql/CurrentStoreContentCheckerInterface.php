<?php

namespace Digidirect\Blog\Sql;

interface CurrentStoreContentCheckerInterface
{
    /**
     * @param int $entityId
     * @param int $storeId
     * @return bool
     */
    public function hasStoreViewContent(int $entityId, int $storeId): bool;
}
