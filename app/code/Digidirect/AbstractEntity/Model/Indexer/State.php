<?php

namespace Digidirect\AbstractEntity\Model\Indexer;

use Magento\Store\Model\Store;

class State
{
    /**
     * @var array
     */
    private $indexByStoreId = [];

    /**
     * @param int $storeId
     * @return bool
     */
    public function isDone(int $storeId)
    {
        if ((int)Store::DEFAULT_STORE_ID == $storeId) {
            return false;
        }

        return $this->indexByStoreId[$storeId] ?? false;
    }

    /**
     * @param int $storeId
     * @return $this
     */
    public function setReindexIsDone(int $storeId)
    {
        $this->indexByStoreId[$storeId] = true;
        return $this;
    }
}
