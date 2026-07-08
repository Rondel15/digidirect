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

class SenderAdmin extends SenderAbstract
{
    protected function getFommatVar($item, $method)
    {
        $rawCommentData = $item['comment'];
        $splitCommentData = explode("#contact_number:", $rawCommentData);
        
        return [
            'send_vars' => [
                'product_name' => $item['product_name'],
                'request_id' => $item['item_id'],
                'status' => $item['status'],
                'storeview' => $this->storeFactory->create()->load($item['store_id'])->getName(),
                'customer_name' => $item['name'], //$this->helperData->genEmailAdmin($item['store_id']),
                'date_time' => $item['date_created'],
                'contact' => $splitCommentData[1],
                'email' => $item['customer_email'],
                'current_price' => $this->formatPrice($item['final_price'], $item['store_id']),
                'requested_price' => $this->formatPrice($item['match_price'], $item['store_id']),
                'url' => ($item['match_url']) ? $item['match_url'] : __('n/a'),
                'comment' => $splitCommentData[0],
            ],
            'email' => $this->helperData->genEmailAdmin($item['store_id']),
            'store_id' => $item['store_id'],
        ];
    }

    protected function getFromSender($item)
    {
        return $this->helperData->getSender($item['store_id']);
    }

    protected function getTemplateSender($storeId)
    {
        return $this->helperData->getTemplateAdmin($storeId);
    }
}