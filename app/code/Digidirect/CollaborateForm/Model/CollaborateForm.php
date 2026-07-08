<?php

namespace Digidirect\CollaborateForm\Model;

class Cform extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    
    protected function _construct()
    {
        $this->_init('Digidirect\CollaborateForm\Model\ResourceModel\CollaborateForm');
    }

}