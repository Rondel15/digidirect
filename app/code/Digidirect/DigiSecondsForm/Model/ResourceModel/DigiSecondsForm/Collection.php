<?php
namespace Digidirect\Cform\Model\ResourceModel\DigiSecondsForm;
 
use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
 
class Collection extends AbstractCollection
{
 
    protected $_idFieldName = \Digidirect\DigiSecondsForm\Model\Cform::CONTECT_ID;
     
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Digidirect\DigiSecondsForm\Model\DigiSecondsForm', 'Digidirect\DigiSecondsForm\Model\ResourceModel\DigiSecondsForm');
    }
 
}