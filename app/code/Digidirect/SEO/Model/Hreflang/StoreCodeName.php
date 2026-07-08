<?php

namespace Digidirect\SEO\Model\Hreflang;

/**
 * Class StoreCodeName
 */
class StoreCodeName
{
    /**
     * @param $store
     * @return mixed
     */
    public function getHreflangData($store)
    {
        return $store->getName();
    }

    /**
     * @param $store
     * @return mixed
     */
    public function getHreflangUrl($store)
    {
        return $store->getCurrentUrl(false);
    }
}
