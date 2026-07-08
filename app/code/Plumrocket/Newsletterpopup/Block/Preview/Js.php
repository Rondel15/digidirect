<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Preview;

use Magento\Framework\View\Element\Template as ViewTemplate;
use Plumrocket\Newsletterpopup\Block\Js as JsBase;
use Plumrocket\Newsletterpopup\Model\Config\Source\Show;

class Js extends JsBase
{

    /**
     * Disable parent logic.
     *
     * @return string
     */
    protected function _toHtml()
    {
        return ViewTemplate::_toHtml();
    }

    public function getPopupArea(): string
    {
        return Show::ON_ACCOUNT_PAGES;
    }

    /**
     * Rewrite settings for preview mode.
     *
     * @return array
     */
    public function getGlobalSettings(): array
    {
        $settings = parent::getGlobalSettings();
        $settings['is_preview'] = true;
        $settings['enable_analytics'] = 0;
        return $settings;
    }
}
