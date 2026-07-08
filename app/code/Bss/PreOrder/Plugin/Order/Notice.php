<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_PreOrder
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\PreOrder\Plugin\Order;

use Bss\PreOrder\Helper\Data;
use Bss\PreOrder\Model\PreOrderAttribute;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class Notice
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * OrderNotice constructor.
     * @param Data $helper
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        Data $helper,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->helper = $helper;
        $this->request = $request;
    }

    /**
     * Extra Note Pre Order Product
     *
     * @param \Magento\Sales\Block\Items\AbstractItems $subject
     * @param \Magento\Framework\DataObject $item
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetItemHtml($subject, $item)
    {
        $listProductPreOrder = $subject->getOrder() ? $subject->getOrder()->getProductPreOrder() : null;
        if ($this->helper->isEnable()
            && $listProductPreOrder !== null
            && $listProductPreOrder !== '[]'
        ) {
            $action = $this->request->getFullActionName();
            if ($this->helper->isEnable()) {
                if ($item->getProductType() == Configurable::TYPE_CODE) {
                    if (strpos($action, 'multishipping_checkout') !== false) {
                        $product = $this->helper->getProductBySku($item->getSku());
                    } else {
                        $product = $this->helper->getProductBySku($item->getProductOptionByCode('simple_sku'));
                    }
                } else {
                    $product = $this->helper->getProductBySku($item->getSku());
                }
                $listProductPreOrder = $this->helper->serializeClass()->unserialize($listProductPreOrder);
                if ($product
                    && $product instanceof \Magento\Catalog\Api\Data\ProductInterface
                    && in_array($product->getId(), array_keys($listProductPreOrder))
                ) {
                    $message = $this->helper->replaceVariableX(
                        $this->helper->getNote(),
                        $this->helper->formatDate($product->getData('pre_oder_from_date')),
                        $this->helper->formatDate($product->getData('pre_oder_to_date'))
                    );
                    return [$item->setDescription($message)];
                }
            }
        }
        return [$item];
    }
}
