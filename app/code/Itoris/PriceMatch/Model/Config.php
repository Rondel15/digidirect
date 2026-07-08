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

class Config extends \Magento\Framework\Model\AbstractModel
{
    const CACHE_TAG = 'itoris_pricematch_config';

    protected $helperData;

    public function __construct
    (
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Itoris\PriceMatch\Helper\Data $helperData,
        ResourceModel\Config $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->helperData = $helperData;
    }

    protected function _construct()
    {
        $this->_init('Itoris\PriceMatch\Model\ResourceModel\Config');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function loadItem($productId, $storeId)
    {
        $resultArray = [];
        $resultArrayDefault = [];
        $systemArray = $this->helperData->getSettings($storeId);
        $connection =  $this->getResource()->getConnection();

        $query =  $connection->select()->from(['conf' => $this->getResource()->getMainTable()])->
        where('conf.store_id = '. $storeId.' AND conf.product_id = '. $productId);

        $buff = $connection->fetchAll($query);
        foreach ($buff as $item){
            $resultArray[$item['attr_code']] = $item['value'];
        }

        $query =  $connection->select()->from(['conf' => $this->getResource()->getMainTable()])->
        where('conf.store_id = 0 AND conf.product_id = '. $productId);



        $buff = $connection->fetchAll($query);
        foreach ($buff as $item){
            $resultArrayDefault[$item['attr_code']] = $item['value'];
        }

  //      var_dump(      $resultArray      );
 //       var_dump(      $resultArrayDefault      );
  //      die;


        if(isset($resultArray['group_list'])){
            $systemArray->setGroupList($resultArray['group_list']);
        }elseif(isset($resultArrayDefault['group_list'])){
            $systemArray->setGroupList($resultArrayDefault['group_list']);
        }
        if(isset($resultArray['link_text'])){
            $systemArray->setLinkText($resultArray['link_text']);
        }elseif(isset($resultArrayDefault['link_text'])){
            $systemArray->setLinkText($resultArrayDefault['link_text']);
        }
        if(isset($resultArray['comment_popup'])){
            $systemArray->setCommentPopup($resultArray['comment_popup']);
        }elseif(isset($resultArrayDefault['comment_popup'])){
            $systemArray->setCommentPopup($resultArrayDefault['comment_popup']);
        }
        if(isset($resultArray['show_link'])){
            $systemArray->setShowLink($resultArray['show_link']);
        }elseif(isset($resultArrayDefault['show_link'])){
            $systemArray->setShowLink($resultArrayDefault['show_link']);
        }

        return $systemArray;
    }
}
