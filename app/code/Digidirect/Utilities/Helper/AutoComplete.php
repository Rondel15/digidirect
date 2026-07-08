<?php

namespace Digidirect\Utilities\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class AutoComplete
 * @package Digidirect\Utilities\Helper
 */
class AutoComplete extends AbstractHelper
{
    const REQUEST_KEY_ADDITIONAL_DATA = 'additionalData';

    /**
     * @return mixed
     */
    public function getAjaxAdditionalData()
    {
        return $this->_request->getParam(self::REQUEST_KEY_ADDITIONAL_DATA, []);
    }
}
