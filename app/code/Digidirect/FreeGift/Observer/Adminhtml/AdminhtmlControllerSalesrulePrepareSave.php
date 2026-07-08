<?php

namespace Digidirect\FreeGift\Observer\Adminhtml;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AdminhtmlControllerSalesrulePrepareSave implements ObserverInterface
{
    /**
     * @param Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        /**
         * @var $request \Magento\Framework\App\RequestInterface
         */
        $request = $observer->getEvent()->getData('request');
        $simpleAction = $request->getPost('simple_action');
        if ($simpleAction != 'freegift_items') {
            return;
        }

        $freeGiftRuleData = $request->getPost('freegiftrule');
        if (empty($freeGiftRuleData['enable_on_pdp']) && empty($freeGiftRuleData['show_desc_on_pdp'])) {
            return;
        }

        $rule = $request->getPost('rule');
        if (empty($rule['conditions'])) {
            return;
        }

        $conditions = $rule['conditions'];
        if (empty($conditions)) {
            return;
        }

        if (count($conditions) > 1) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'It is impossible to use free gift options <b>‘%1’</b> or <b>‘%2’</b> with any rule conditions.',
                    __('Enable Free Gift Block On Product Detail'),
                    __('Show description on product details')
                )
            );
        }
    }
}
