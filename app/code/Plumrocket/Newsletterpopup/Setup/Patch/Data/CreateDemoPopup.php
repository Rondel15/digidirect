<?php
/**
 * @package     Plumrocket_NewsletterPopup
 * @copyright   Copyright (c) 2022 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Setup\Patch\Data;

use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\State;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Plumrocket\Newsletterpopup\Model\Popup\GetDefaultRule;
use Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\CollectionFactory as PopupCollectionFactory;

/**
 * @since 4.3.0
 */
class CreateDemoPopup implements DataPatchInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\FormFieldFactory
     */
    private $formFieldFactory;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\PopupFactory
     */
    private $popupFactory;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\CollectionFactory
     */
    private $popupCollectionFactory;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\Popup\GetDefaultRule
     */
    private $getDefaultRule;

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface                       $moduleDataSetup
     * @param \Plumrocket\Newsletterpopup\Model\FormFieldFactory                      $formFieldFactory
     * @param \Plumrocket\Newsletterpopup\Model\PopupFactory                          $popupFactory
     * @param \Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\CollectionFactory $popupCollectionFactory
     * @param \Magento\Framework\App\State                                            $state
     * @param \Plumrocket\Newsletterpopup\Model\Popup\GetDefaultRule                  $getDefaultRule
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Plumrocket\Newsletterpopup\Model\FormFieldFactory $formFieldFactory,
        \Plumrocket\Newsletterpopup\Model\PopupFactory $popupFactory,
        PopupCollectionFactory $popupCollectionFactory,
        State $state,
        GetDefaultRule $getDefaultRule
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->formFieldFactory = $formFieldFactory;
        $this->popupFactory = $popupFactory;
        $this->popupCollectionFactory = $popupCollectionFactory;
        try {
            $state->setAreaCode('adminhtml');
        } catch (\Exception $e) {
        }
        $this->getDefaultRule = $getDefaultRule;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $isPopupExist = $this->popupCollectionFactory->create()
            ->getSize();

        if (! $isPopupExist) {
            $data = [
                'template_id' => 20,
                'name' => 'Newsletter Popup $10 - Default Template',
                'status' => 1,
                'coupon_code' => 0,
                'start_date' => null,
                'end_date' => null,
                'success_page' => '__stay__',
                'custom_success_page' => '',
                'send_email' => 1,
                'template_name' => 'prnewsletterpopup-default',
                'display_popup' => 'after_time_delay',
                'delay_time' => 5,
                'cookie_time_frame' => 30,
                'store_id' => '0',
                'show_on' => 'all',
                'devices' => 'all',
                'customers_groups' => '0',
                'text_title' => 'GET $10 OFF YOUR FIRST ORDER',
                'text_description' => '<p>Join Magento Store List and Save!<br />Subscribe Now &amp; Receive a ' .
                    '$10 OFF coupon in your email!</p>',
                'text_note' => '<p>Enter your email</p>',
                'text_success' => '<div class="message-title"><h2>ENJOY $10 OFF</h2><p>entire purchase</p></div><div class="coupon_wrp"><div class="coupon-message">Enter Coupon Code At Checkout:</div><div class="coupon-use">{{coupon_code}}</div></div><div class="coupon-expiration"><span>Hurry! This Offer Ends in 2 HOURS!</span></div>',
                'text_submit' => 'Sign Up Now',
                'text_cancel' => 'Hide',
                'code_length' => 12,
                'code_format' => 'alphanum',
                'code_prefix' => '',
                'code_suffix' => '',
                'code_dash' => 0,
                'conditions' => $this->getDefaultRule->execute(),
            ];

            $demoPopup = $this->popupFactory->create()->addData($data)->initConditionsSerialized()->save();

            // Add email field for first demo popup.
            if ($demoPopup->getId()) {
                $emailField = [
                    'name' => 'email',
                    'label' => 'Email',
                    'enable' => 1,
                    'sort_order' => 10,
                    'popup_id' => $demoPopup->getId(),
                ];

                $this->formFieldFactory->create()->setData($emailField)->save();
            }
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
