<?php

namespace Digidirect\ExtendedShippingRates\Block\Adminhtml\ExtendedShippingRates\Zone\Grid\Button;

/**
 * Class Upload
 * @package Digidirect\ExtendedShippingRates\Block\Adminhtml\ExtendedShippingRates\Zone\Grid\Button
 */
class Upload extends \Magento\Backend\Block\Widget
{
    const UPLOAD_URL = 'digidirect_extendedshippingrates/extendedshippingrates_zone/upload';

    /**
     * @var string
     */
    protected $_template = 'Digidirect_ExtendedShippingRates::zone/grid/button/upload_form.phtml';

    /**
     * @return string
     */
    public function getUploadUrl()
    {
        return $this->getUrl(self::UPLOAD_URL);
    }
}
