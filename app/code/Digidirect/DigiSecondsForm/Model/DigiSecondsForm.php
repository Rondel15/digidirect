<?php

namespace Digidirect\DigiSecondsForm\Model;

class Cform extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    
    protected function _construct()
    {
        $this->_init('Digidirect\DigiSecondsForm\Model\ResourceModel\DigiSecondsForm');
    }

}