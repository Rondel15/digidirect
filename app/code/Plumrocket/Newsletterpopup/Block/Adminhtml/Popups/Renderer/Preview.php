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
use Plumrocket\Newsletterpopup\Helper\Data;

class Preview extends AbstractRenderer
{
    protected $_dataHelper;
    protected $_adminhtmlHelper;

    public function __construct(
        Context $context,
        Data $dataHelper,
        Adminhtml $adminhtmlHelper,
        array $data = []
    ) {
        $this->_dataHelper = $dataHelper;
        $this->_adminhtmlHelper = $adminhtmlHelper;
        parent::__construct($context, $data);
    }

    /**
     * @param \Plumrocket\Newsletterpopup\Model\Popup $row
     * @return string
     */
    public function render(DataObject $row)
    {
        if ($row->isModal()) {
            return sprintf(
                '<a href="%s" target="_blank"><button><span>%s</span></button></a>',
                $this->_dataHelper->validateUrl(
                    $this->_adminhtmlHelper->getFrontendUrl(
                        'prnewsletterpopup/index/preview',
                        ['id' => $row->getId()]
                    )
                ),
                __('Preview')
            );
        }

        return '';
    }
}
