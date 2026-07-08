<?php
/**
 * @author      WebPanda
 * @package     WebPanda_ReviewNotification
 * @copyright   Copyright (c) WebPanda (https://webpanda-solutions.com/)
 * @license     https://webpanda-solutions.com/license-agreement
 */

namespace WebPanda\SalesProductImage\Block\Adminhtml\Order\Create\Search\Grid\Renderer;

/**
 * Class Image
 * @package WebPanda\SalesProductImage\Block\Adminhtml\Order\Create\Search\Grid\Renderer
 */
class Image extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    /**
     * @var \WebPanda\SalesProductImage\Helper\Data
     */
    protected $helper;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \WebPanda\SalesProductImage\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Render product name to add Configure link
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        return $this->helper->renderImage($row->getId(), 'admin', 'none');
    }
}
