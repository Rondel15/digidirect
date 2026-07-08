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

use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class Delete  implements ButtonProviderInterface
{
    protected $reguest;
    protected $urlBuilder;

    public function __construct(
        \Magento\Framework\App\RequestInterface $reguest,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->reguest = $reguest;
        $this->urlBuilder = $urlBuilder;
    }

    public function getButtonData()
    {
        $data = [
            'label' => __('Delete'),
            'class' => 'delete',
            'id' => 'itoris_item_delete',
            'data_attribute' => [
                'url' => $this->getDeleteUrl()
            ],
            'on_click' => sprintf("location.href = '%s';", $this->getDeleteUrl()),
            'sort_order' => 20,
        ];
        return $data;
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->urlBuilder->getUrl('itorispm/action/delete', ['id' => $this->reguest->getParam('id')]);
    }

}