<?php
namespace Digidirect\CollectStoreLocator\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class Data
 * @package Digidirect\CollectStoreLocator\Controller\Ajax
 */
class Data extends AbstractHelper
{
    /**
     * @return string
     */
    public function getAjaxLocatorBlockUrl()
    {
        return $this->_getUrl('digidirectcollectlocator/ajax_locator/block');
    }
}
