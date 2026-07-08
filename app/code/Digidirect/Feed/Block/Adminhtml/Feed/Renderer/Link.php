<?php

namespace Digidirect\Feed\Block\Adminhtml\Feed\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class Link extends AbstractRenderer
{
    /**
     * {@inheritdoc}
     */
    public function render(DataObject $row)
    {
        /** @var \Digidirect\Feed\Model\Feed $row */

        $url = $row->getUrl();
        if ($url) {
            return '<a href="' . $url . '" target="_blank">' . $row->getFilename() . '</a>';
        }

        return $row->getFilename();
    }
}
