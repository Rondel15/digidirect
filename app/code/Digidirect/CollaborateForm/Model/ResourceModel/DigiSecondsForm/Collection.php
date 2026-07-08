<?php
namespace Digidirect\CollaborateForm\Model\ResourceModel\CollaborateForm;
 
use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
 
class Collection extends AbstractCollection
{
 
    protected $_idFieldName = \Digidirect\CollaborateForm\Model\CollaborateForm::CONTECT_ID;
     
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Digidirect\CollaborateForm\Model\CollaborateForm', 'Digidirect\CollaborateForm\Model\ResourceModel\CollaborateForm');
    }
 
}