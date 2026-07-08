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

namespace Itoris\PriceMatch\Observer;
use Magento\Framework\Event\ObserverInterface;

class SaveSettingsProduct implements ObserverInterface
{
    protected $tabs = false;
    protected $_categoryItoris;
    protected $_request;
    protected $_storeManager;
    protected $_productItoris;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Itoris\PriceMatch\Model\ConfigFactory $configItorisFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->_request = $request;
        $this->_productItoris = $configItorisFactory;
        $this->_storeManager = $storeManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $requestParams =  $this->_request->getParams();
        if( !isset($requestParams['itoris_pricematch_link_text']) ){
            return;
        }

        $productId = $observer->getEvent()->getProduct()->getId();

        $productModel = $this->_productItoris->create();
        $productCollection = $productModel->getCollection()
            ->addFieldToFilter('store_id', $this->getStoreId())
            ->addFieldToFilter('product_id', $productId);

        $attrProductArray = [];
        $attrItemIdArray = [];

        foreach($productCollection->getData() as $item){
            $attrProductArray[$item['attr_code']] = $item['value'];
            $attrItemIdArray[$item['attr_code']] = $item['item_id'];
        }

        $attrPOST = $this->_request->getParam('itoris_pricematch_show_link');
        if(isset($attrItemIdArray['show_link'])){
            $this->_removeItem($attrItemIdArray['show_link']);
        }
        $this->_saveItem('show_link', $attrPOST, $productId);

        $attrPOST = $this->_request->getParam('itoris_pricematch_group_list_hidden');
        $attrCheckPOST = $this->_request->getParam('itoris_pricematch_group_list_check');
        if($attrCheckPOST) {
            if(isset($attrItemIdArray['group_list'])){
                $this->_removeItem($attrItemIdArray['group_list']);
            }
        }else{
            if(isset($attrItemIdArray['group_list'])){
                $this->_removeItem($attrItemIdArray['group_list']);
            }
            $this->_saveItem('group_list', $attrPOST, $productId);
        }

        $attrPOST = $this->_request->getParam('itoris_pricematch_link_text');
        $attrCheckPOST = $this->_request->getParam('itoris_pricematch_link_text_check');
        if($attrCheckPOST) {
            if(isset($attrItemIdArray['link_text'])){
                $this->_removeItem($attrItemIdArray['link_text']);
            }
        }else{
            if(isset($attrItemIdArray['link_text'])){
                $this->_removeItem($attrItemIdArray['link_text']);
            }
            $this->_saveItem('link_text', $attrPOST, $productId);
        }

        $attrPOST = $this->_request->getParam('itoris_pricematch_comment_popup');
        $attrCheckPOST = $this->_request->getParam('itoris_pricematch_comment_popup_check');
        if($attrCheckPOST) {
            if(isset($attrItemIdArray['comment_popup'])){
                $this->_removeItem($attrItemIdArray['comment_popup']);
            }
        }else{
            if(isset($attrItemIdArray['comment_popup'])){
                $this->_removeItem($attrItemIdArray['comment_popup']);
            }
            $this->_saveItem('comment_popup', $attrPOST, $productId);
        }
    }

    private function _saveItem( $attr_code, $value, $productId )
    {
        /** @var \Itoris\PriceMatch\Model\Config $productModel */
        $productModel = $this->_productItoris->create();
        $productModel->setProductId( $productId )
            ->setStoreId($this->getStoreId())
            ->setValue($value)
            ->setAttrCode($attr_code);
        $productModel->save();
    }

    private function _removeItem($attrId)
    {
        $productModel = $this->_productItoris->create();
        $productModel->load($attrId);
        $productModel->delete();
    }

    private function getStoreId()
    {
        return $this->_request->getParam('store', 0);
    }
}