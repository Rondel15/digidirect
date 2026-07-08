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

namespace Itoris\PriceMatch\Helper;

use Magento\Catalog\Api\Data\ProductInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const SCOPE_TYPE_STORES               = 'store';
    const MODULE_ENABLED                  = 'itoris_pricematch/general/module_enabled';
    const SEND_EMAIL_ADMIN                = 1;

    const CONFIG_SEND_EMAIL_ADMIN         = 'itoris_pricematch/general/send_email_admin';
    const CONFIG_SENDER_ADMIN_EMAIL       = 'itoris_pricematch/general/sender_admin_email';
    const CONFIG_TEMPLATE_ADMIN_EMAIL     = 'itoris_pricematch/general/template_admin_email';
    const CONFIG_SENDER_CUSTOMER_EMAIL    = 'itoris_pricematch/general/sender_customer_email';
    const CONFIG_TEMPLATE_CUSTOMER_EMAIL  = 'itoris_pricematch/general/template_customer_email';
    const CONFIG_GROUP_LIST               = 'itoris_pricematch/general/group_list';
    const CONFIG_LINK_TEXT                = 'itoris_pricematch/general/link_text';
    const CONFIG_COMMENT_POPUP            = 'itoris_pricematch/general/comment_popup';

    protected $_backendConfig;
    protected $_registry;
    protected $_storeManager;
    protected $timezone;
    protected $_layout;
    protected $helperPrice;
    protected $escaper;
    protected $resourceConnection;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Backend\App\ConfigInterface $backendConfig,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\Pricing\Helper\Data $helperPrice,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->helperPrice        = $helperPrice;
        $this->timezone           = $timezone;
        $this->escaper            = $escaper;
        $this->_layout            = $layout;
        $this->_registry          = $registry;
        $this->_storeManager      = $storeManager;
        $this->_backendConfig     = $backendConfig;
        $this->resourceConnection = $resourceConnection;

        parent::__construct($context);
    }


    public function isEnabled() {
        return (int)$this->scopeConfig->getValue('itoris_pricematch/general/module_enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            && count(explode('|', $this->_backendConfig->getValue('itoris_core/installed/Itoris_PriceMatch'))) == 2;
    }

    public function getScopeConfig() {
        return $this->scopeConfig;
    }

    public function getStoreId() {
        return $this->_storeManager->getStore()->getId();
    }

    public function getBackendConfig() {
        return $this->_backendConfig;
    }

    public function getSettings($store)
    {
        if($this->_registry->registry('itoris_pricematch_settings')){
            return  $this->_registry->registry('itoris_pricematch_settings');
        }

        $registrySettings =  new \Magento\Framework\DataObject([
            'send_email_admin' => $this->scopeConfig
                ->getValue(self::CONFIG_SEND_EMAIL_ADMIN, self::SCOPE_TYPE_STORES, $store),
            'sender_admin_email' => $this->scopeConfig
                ->getValue(self::CONFIG_SENDER_ADMIN_EMAIL, self::SCOPE_TYPE_STORES, $store),
            'template_admin_email' => $this->scopeConfig
                ->getValue(self::CONFIG_TEMPLATE_ADMIN_EMAIL, self::SCOPE_TYPE_STORES, $store),
            'sender_customer_email' => $this->scopeConfig
                ->getValue(self::CONFIG_SENDER_CUSTOMER_EMAIL, self::SCOPE_TYPE_STORES, $store),
            'template_customer_email' => $this->scopeConfig
                ->getValue(self::CONFIG_TEMPLATE_CUSTOMER_EMAIL, self::SCOPE_TYPE_STORES, $store),
            'group_list' => $this->scopeConfig
                ->getValue(self::CONFIG_GROUP_LIST, self::SCOPE_TYPE_STORES, $store),
            'link_text' => $this->scopeConfig
                ->getValue(self::CONFIG_LINK_TEXT, self::SCOPE_TYPE_STORES, $store),
            'comment_popup' => $this->scopeConfig
                ->getValue(self::CONFIG_COMMENT_POPUP, self::SCOPE_TYPE_STORES, $store),

        ]);
        $this->_registry->register('itoris_pricematch_settings',$registrySettings);
        return $registrySettings;
    }

    public function getSender($storeId) {
        $sender = 0;

        switch ( $this->getSettings($storeId)->getTemplateSender() ){
            case 0:
                $sender = [
                    'name' => $this->escaper->escapeHtml( $this->scopeConfig->getValue('trans_email/ident_general/name') ),
                    'email' => $this->escaper->escapeHtml( $this->scopeConfig->getValue('trans_email/ident_general/email') ),
                ];
                break;
            case 1:
                $sender = [
                    'name' => $this->escaper->escapeHtml( $this->scopeConfig->getValue('trans_email/ident_sales/name') ),
                    'email' => $this->escaper->escapeHtml( $this->scopeConfig->getValue('trans_email/ident_sales/email') ),
                ];
                break;
            case 2:
                $sender = [
                    'name' => $this->escaper->escapeHtml( $this->scopeConfig->getValue('trans_email/ident_support/name') ),
                    'email' => $this->escaper->escapeHtml( $this->scopeConfig->getValue('trans_email/ident_support/email') ),
                ];
                break;
            case 3:
                $sender = [
                    'name' => $this->escaper->escapeHtml( $this->scopeConfig->getValue('trans_email/ident_custom1/name') ),
                    'email' => $this->escaper->escapeHtml( $this->scopeConfig->getValue('trans_email/ident_custom1/email') ),
                ];
                break;
            case 4:
                $sender = [
                    'name' => $this->escaper->escapeHtml( $this->scopeConfig->getValue('trans_email/ident_custom2/name') ),
                    'email' => $this->escaper->escapeHtml( $this->scopeConfig->getValue('trans_email/ident_custom2/email') ),
                ];
                break;
        }

        return $sender;
    }

    public function getTemplateCustomer($storeId) {
        return $this->escaper->escapeHtml( $this->getSettings($storeId)->getTemplateCustomerEmail() );
    }

    public function getTemplateAdmin($storeId) {
        return $this->escaper->escapeHtml( $this->getSettings($storeId)->getTemplateAdminEmail() );
    }

    public function getBlock($namespace) {
        return $this->_layout->createBlock($namespace);
    }

    public function getSettingsFinal($productId, $storeId)
    {
        $buffArr = [];
        $resultArray = [];
        $resultArrayDefault = [];
        if($productId){
            $conn = $this->resourceConnection->getConnection();

            $query =  $conn->select()->from(['conf' => $this->resourceConnection->getTableName('itoris_pricematch_setting')])->
            where('conf.store_id = '. $storeId.' AND conf.product_id = '. $productId);

            $buff = $conn->fetchAll($query);
            if($buff){
                foreach ($buff as $item){
                    $resultArray[$item['attr_code']] = $item['value'];
                }

                $buffArr['show_link'] = $resultArray['show_link'];
            }

            $query =  $conn->select()->from(['conf' => $this->resourceConnection->getTableName('itoris_pricematch_setting')])->
            where('conf.store_id = 0 AND conf.product_id = '. $productId);

            $buff = $conn->fetchAll($query);

            if($buff){
                foreach ($buff as $item){
                    $resultArrayDefault[$item['attr_code']] = $item['value'];
                }

                $buffArr['show_link'] = $resultArrayDefault['show_link'];
            }
        }

        foreach ($this->getSettings($storeId)->getData() as $key => $item){
            if($storeId){
                if( isset($resultArray[$key]) ){
                    $buffArr[$key] = $resultArray[$key];
                    $buffArr[$key.'_check'] = '';
                }elseif(isset($resultArrayDefault[$key])){
                    $buffArr[$key] = $resultArrayDefault[$key];
                    $buffArr[$key.'_check'] = 1;
                }else{
                    $buffArr[$key] = $item;
                    $buffArr[$key.'_check'] = 1;
                }
            }else{
                if(isset($resultArrayDefault[$key])){
                    $buffArr[$key] = $resultArrayDefault[$key];
                    $buffArr[$key.'_check'] = '';
                }else{
                    $buffArr[$key] = $item;
                    $buffArr[$key.'_check'] = 1;
                }
            }
        }

        return $buffArr;
    }

    public function checkSendAdmin($store) {
        $settings = $this->getSettings($store);

        return ($settings->getSendEmailAdmin() == self::SEND_EMAIL_ADMIN) ? true : false;
    }

    public function genEmailAdmin($store) {
        $settings = $this->getSettings($store);

        return $settings->getSenderAdminEmail();
    }
}
