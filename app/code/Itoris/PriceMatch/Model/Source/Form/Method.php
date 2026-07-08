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

namespace Itoris\PriceMatch\Model\Source\Form;

class Method implements \Magento\Framework\Option\ArrayInterface
{
    protected $request;
    protected $priceCurrency;
    protected $collectionFactory;

    public function __construct
    (
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Itoris\PriceMatch\Model\ResourceModel\PriceMatch\CollectionFactory $collectionFactory,
        \Magento\Framework\App\RequestInterface $request
    )
    {
        $this->collectionFactory = $collectionFactory;
        $this->priceCurrency = $priceCurrency;
        $this->request = $request;
    }

    public function toOptionArray()
    {
        $item = $this->collectionFactory->create()->sendItemById($this->request->getParam('id'));
        $lower = $this->priceCurrency->format(
            $item['match_price'],
            false,
            \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION,
            $item['store_id']
        );
        $diffPrice =$this->priceCurrency->format(
            $item['final_price']-$item['match_price'],
            false,
            \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION,
            $item['store_id']
        );

        return [
            [
                'label' => __('Lower the product price to %1', $lower),
                'value' => 'lower'
            ],
            [
                'label' => __('Send a one-time coupon for the price difference of %1', $diffPrice),
                'value' => 'coupon'
            ],
            [
                'label' => __('Reject request'),
                'value' => 'reject'
            ],
        ];

    }
}
