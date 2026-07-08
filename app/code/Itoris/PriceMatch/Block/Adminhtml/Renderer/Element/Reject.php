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

namespace Itoris\PriceMatch\Block\Adminhtml\Renderer\Element;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Ui\Component\Control\Container;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class Reject  implements ButtonProviderInterface
{
    protected $context;
    protected $registry;
    protected $reguest;
    protected $urlBuilder;
    protected $priceMatch;

    public function __construct(
        \Magento\Framework\App\RequestInterface $reguest,
        \Itoris\PriceMatch\Model\PriceMatch $priceMatch,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->reguest = $reguest;
        $this->priceMatch = $priceMatch;
        $this->urlBuilder = $urlBuilder;
    }

    public function getButtonData()
    {
        $status =  $this->priceMatch->load($this->reguest->getParam('id'))->getStatus();
        $data = [];
        if($status == \Itoris\PriceMatch\Model\PriceMatch::STATUS_PENDING) {
            $data = [
                'label' => __('Reject'),
                'class' => 'reject',
                'id' => 'itoris_item_reject',
                'data_attribute' => [
                    'url' => $this->getDeleteUrl()
                ],
                'on_click' => sprintf("location.href = '%s';", $this->getDeleteUrl()),
                'sort_order' => 25,
            ];
        }

        return $data;
    }

    public function getDeleteUrl()
    {
        return $this->urlBuilder->getUrl('*/action/reject', ['id' => $this->reguest->getParam('id')]);
    }

}