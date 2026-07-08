<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Digi\Qantas\Model\ResourceModel;

class QantasModel extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    


    
	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	)
	{
		parent::__construct($context);
	}
	
	protected function _construct()
	{
		$this->_init('qantas_qff_member', 'qff_number'); 
                $this->_isPkAutoIncrement = false;
	}

}