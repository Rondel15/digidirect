<?php

namespace Digidirect\ProductOverlay\Block;

use Magento\Store\Model\ScopeInterface;

/**
 * Class Overlay
 * @package Digidirect\ProductOverlay\Block
 */
class Overlay extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Digidirect\ProductOverlay\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Digidirect\ProductOverlay\Model\Overlays
     */
    protected $_overlay;

    /**
     * Overlay constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Digidirect\ProductOverlay\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Digidirect\ProductOverlay\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_helper      = $helper;
        $this->setTemplate('Digidirect_ProductOverlay::overlay.phtml');
    }

    /**
     * @param \Digidirect\ProductOverlay\Model\Overlays $overlay
     * @return $this
     */
    public function setOverlay(\Digidirect\ProductOverlay\Model\Overlays $overlay)
    {
        $this->_overlay = $overlay;
        return $this;
    }

    /**
     * @return \Digidirect\ProductOverlay\Model\Overlays
     */
    public function getOverlay()
    {
        return $this->_overlay;
    }

    /**
     * Get container path from module settings
     *
     * @return string
     */
    public function getContainerPath()
    {
        return $this->_helper->getContainerPath($this->getOverlay()->getMode());
    }

    /**
     * Get image url withmode and site url
     *
     * @return string
     */
    public function getImageScr()
    {
        $img = $this->_overlay->getValue('img');

        return $this->_helper->getImageUrl($img);
    }
}
