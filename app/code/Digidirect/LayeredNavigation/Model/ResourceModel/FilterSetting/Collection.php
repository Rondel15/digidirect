<?php
namespace Digidirect\LayeredNavigation\Model\ResourceModel\FilterSetting;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Construct
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Digidirect\LayeredNavigation\Model\FilterSetting',
            'Digidirect\LayeredNavigation\Model\ResourceModel\FilterSetting'
        );
    }
}
