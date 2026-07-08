<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Shopbybrand\Block\Adminhtml\Related\Edit\Tab\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

/**
 * Class Store
 * @package Mageplaza\Shopbybrand\Block\Adminhtml\Related\Edit\Tab\Renderer
 */
class Store extends AbstractRenderer
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @param Context $context
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        parent::__construct($context, $data);
    }

    /**
     * @param DataObject $row
     *
     * @return string
     */
    public function render(DataObject $row)
    {
        $storeId = $row->getData($this->getColumn()->getIndex());

        $storeHtml = $this->getLayout()->createBlock(Template::class)->setData([
            'storeId' => $storeId,
            'stores'  => $this->_systemStore->getStoreCollection(),
        ])->setTemplate('Mageplaza_Shopbybrand::related_brand/store.phtml')->toHtml();

        return $storeHtml;
    }
}
