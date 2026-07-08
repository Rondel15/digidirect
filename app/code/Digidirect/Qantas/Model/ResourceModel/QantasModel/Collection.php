<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace  Digi\Qantas\Model\ResourceModel\QantasModel;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

   
  


        protected $_idFieldName = 'qff_number';
	protected $_eventPrefix = 'qantas_qff_member';
	protected $_eventObject = 'qantas_qff_member';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
        $this->_init(\Digi\Qantas\Model\QantasModel::class, \Digi\Qantas\Model\ResourceModel\QantasModel::class);
	}


}