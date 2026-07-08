<?php

namespace Digidirect\Collect\Model\Config;

class Data extends \Magento\Framework\Config\Data
{
    /**
     * GetStorageForHandle
     *
     * @param string $handle
     * @return bool|[]
     */
    public function getStorageForHandle($handle = null)
    {
        $storages = $this->get('placestorages');

        if (isset($storages[$handle])) {
            return $storages[$handle];
        } else {
            if ($handle == null) {
                return $storages;
            }
        }

        return false;
    }
}
