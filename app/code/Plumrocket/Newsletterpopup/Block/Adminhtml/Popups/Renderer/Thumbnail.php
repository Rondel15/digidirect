<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\Popups\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Plumrocket\Newsletterpopup\Helper\Adminhtml;

class Thumbnail extends AbstractRenderer
{
    protected $_adminhtmlHelper;

    public function __construct(
        Context $context,
        Adminhtml $adminhtmlHelper,
        array $data = []
    ) {
        $this->_adminhtmlHelper = $adminhtmlHelper;
        parent::__construct($context, $data);
    }

    public function render(DataObject $row)
    {
        $path = $this->_adminhtmlHelper->getScreenUrl($row);

        $html = '';
        if ($path !== false) {
            $html = '<div style="text-align: center;"><img ';
            $html .= 'id="' . $this->getColumn()->getId() . '" ';
            $html .= 'src="' . $path . '?b=' . time() . '" ';
            $html .= 'class="grid-image ' . $this->getColumn()->getInlineCss() . '" ';
            $html .= 'style="height: 85px; max-width: 200px;" /></div>';
        }
        return $html;
    }
}
