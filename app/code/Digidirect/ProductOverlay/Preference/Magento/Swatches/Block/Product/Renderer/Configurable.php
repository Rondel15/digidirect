<?php
namespace Digidirect\ProductOverlay\Preference\Magento\Swatches\Block\Product\Renderer;

class Configurable extends \Magento\Swatches\Block\Product\Renderer\Configurable
{
    const CUSTOM_TEMPLATE = 'Digidirect_ProductOverlay::product/view/renderer.phtml';

    /**
     * @return string
     */
    protected function getRendererTemplate()
    {
        return self::CUSTOM_TEMPLATE;
    }
}
