<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Popup\Fields;

class Gender extends Field
{
    public function getGenderOptions()
    {
        return $this->_customer->getAttribute('gender')->getSource()->getAllOptions(false);
    }
}
