<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Digi\Qantas\Model;

class QantasModel extends \Magento\Framework\Model\AbstractModel implements  \Magento\Framework\DataObject\IdentityInterface
{
   const CACHE_TAG = 'qantas_qff_member';
 
   protected $_cacheTag = 'qantas_qff_member';

   protected $_eventPrefix = 'qantas_qff_member';

    protected function _construct()
    {
        $this->_init(\Digi\Qantas\Model\ResourceModel\QantasModel::class);
    }
 

    // public function getEntityId()
    // {
    //     return $this->getData('user_id');
    // }

    // public function getEntityEmail()
    // {
    //     return $this->getData('email');
    // }

    // public function getEntityPassword()
    // {
    //     return $this->getData('password');
    // }
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getQffNumber()];
    }

    

	public function getDefaultValues()
	{
		$values = [];

		return $values;
	}
}