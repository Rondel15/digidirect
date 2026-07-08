<?php
namespace Digidirect\ProductOverlay\Preference\Magento\Swatches\Block\Product\Renderer\Listing;

class Configurable extends \Magento\Swatches\Block\Product\Renderer\Listing\Configurable
{
    const CUSTOM_TEMPLATE = 'Digidirect_ProductOverlay::product/listing/renderer.phtml';

    /**
     * @return string
     */
    protected function getRendererTemplate()
    {
        return self::CUSTOM_TEMPLATE;
    }
}
