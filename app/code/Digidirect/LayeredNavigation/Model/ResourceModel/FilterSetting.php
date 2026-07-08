<?php
namespace Digidirect\LayeredNavigation\Model\ResourceModel;

class FilterSetting extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Construct
     * @return void
     */
    protected function _construct()
    {
        $this->_init('digidirect_layerednavigation_filter_setting', 'setting_id');
    }
}
