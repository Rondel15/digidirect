<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Config\Source;

class Show extends Base
{
    const ON_ALL_PAGES      = 'all';
    const ON_HOME_PAGE      = 'home';
    const ON_CATEGORY_PAGES = 'category';
    const ON_PRODUCT_PAGES  = 'product';
    const ON_CMS_PAGES      = 'cms';
    const ON_ACCOUNT_PAGES  = 'account';

    public function toOptionHash()
    {
        return [
            // self::ON_ALL_PAGES       => __('All pages (Excluding account pages)'),
            self::ON_HOME_PAGE       => __('Home page'),
            self::ON_CATEGORY_PAGES  => __('Category pages'),
            self::ON_PRODUCT_PAGES   => __('Product pages'),
            self::ON_CMS_PAGES       => __('CMS pages'),
        ];
    }
}
