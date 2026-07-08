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

class SenderCustomer extends SenderAbstract
{
    protected function getFommatVar($item, $method)
    {
        $checkMethodCoupon = ($method == PriceMatch::METHOD_COUPON ) ? true : false;
        $checkMethodLower = ($method == PriceMatch::METHOD_LOWER ) ? true : false;
        $couponCode = isset($item['coupon_code']) ? $item['coupon_code'] : null;
        $url = $this->productRepository->getById( $item['product_id'], $item['store_id'] )->getUrlInStore();
        $currentStatus = ($method == PriceMatch::METHOD_COUPON ||  $method == PriceMatch::METHOD_LOWER) ? PriceMatch::STATUS_APPROVED : PriceMatch::STATUS_REJECTED;

        return [
            'send_vars' => [
                'customer_name' => $item['customer_name'],
                'check_method_coupon' => $checkMethodCoupon,
                'check_method_lower' => $checkMethodLower,
                'product_name' => $item['product_name'],
                'difference' => $this->formatPrice($item['final_price'] - $item['match_price'], $item['store_id']),
                'coupon' => $couponCode,
                'requested_price' => $this->formatPrice($item['match_price'], $item['store_id']),
                'link' => $url,
                'link_name' => explode('?',$url)[0],
                'admin_response' => $item['admin_response'],
                'storeview' => $this->storeFactory->create()->load($item['store_id'])->getName(),
                'status' => ucfirst($currentStatus),
            ],
            'email' => $item['customer_email'],
            'store_id' => $item['store_id'],
        ];
    }

    protected function getFromSender($item)
    {
        return $this->helperData->getSender($item['store_id']);
    }

    protected function getTemplateSender($storeId)
    {
        return $this->helperData->getTemplateCustomer($storeId);
    }
}