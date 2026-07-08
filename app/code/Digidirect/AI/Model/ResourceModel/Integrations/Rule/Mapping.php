<?php
namespace Digidirect\AI\Model\ResourceModel\Integrations\Rule;

class Mapping extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('digidirect_ai_mapping_data', 'id');
    }
}
