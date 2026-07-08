<?php
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_M2_ITORIS_PRICE_MATCH
 * @copyright  Copyright (c) 2018 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

namespace Itoris\PriceMatch\Model;

class PriceMatch extends \Magento\Framework\Model\AbstractModel
{
    const CACHE_TAG = 'itoris_pricematch_data';

    const STATUS_PENDING    ='pending';
    const STATUS_REJECTED    ='rejected';
    const STATUS_APPROVED    ='approved';

    const METHOD_LOWER = 'lower';
    const METHOD_REJECT = 'reject';
    const METHOD_COUPON = 'coupon';

    protected $customerSession;

    public function __construct
    (
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        ResourceModel\PriceMatch $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->customerSession = $customerSession;
    }

    protected function _construct()
    {
        $this->_init('Itoris\PriceMatch\Model\ResourceModel\PriceMatch');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getItemIdStatusPending($productId, $storeId, $byRequest=null, $email=null)
    {
        $connection =  $this->getResource()->getConnection();
        $byRequest = $byRequest ? (' AND pm1.by_request = '.$connection->quote($byRequest) ) : '';
        if($this->customerSession->isLoggedIn()){
            $query =  $connection->select()->from(['pm1' => $this->getResource()->getMainTable()])->
            where('pm1.store_id = '. $storeId.' AND pm1.product_id = '. $productId.' AND pm1.customer_id = '.
                $this->customerSession->getCustomerId().$byRequest." AND pm1.status = '". self::STATUS_PENDING."'" );
        }else{
            $query =  $connection->select()->from(['pm1' => $this->getResource()->getMainTable()])->
            where('pm1.store_id = '. $storeId.' AND pm1.product_id = '. $productId.' AND pm1.email = '.$connection->quote($email).$byRequest.
                " AND pm1.status = '". self::STATUS_PENDING."'");
        }
        $buff = $connection->fetchAll($query);

        if(isset($buff[0]['item_id'])){
            return $buff[0]['item_id'];
        }

        return null;
    }
}