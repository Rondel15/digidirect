<?php

namespace Digidirect\ProductOverlay\Block\Adminhtml\Overlays\Renderer;

use Magento\Framework\DataObject;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;

/**
 * Class Image
 * @package Digidirect\ProductOverlay\Block\Adminhtml\Overlays\Renderer
 */
class Image extends AbstractRenderer
{
    /**
     * @var \Digidirect\ProductOverlay\Helper\Data
     */
    protected $_helper;

    /**
     * Image constructor.
     * @param \Digidirect\ProductOverlay\Helper\Data $helper
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(
        \Digidirect\ProductOverlay\Helper\Data $helper,
        \Magento\Backend\Block\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_helper = $helper;
    }

    /**
     * Renders grid column
     *
     * @param DataObject $row
     * @return \Magento\Framework\Phrase|mixed|string
     */
    public function _getValue(DataObject $row)
    {
        $defaultValue = $this->getColumn()->getDefault();
        $data         = parent::_getValue($row);
        $string       = $data === null ? $defaultValue : $data;

        $url = $this->_helper->getImageUrl($string);
        if ($url) {
            $string = '<img src="' . $url . '"
                            title="' . $string . '"
                            alt="' . $string . '"
                            style="max-width: 150px;"
                       >';
        } else {
            $string = __('Image doesn`t exist.');
        }

        return $string;
    }
}
