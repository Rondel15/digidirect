<?php

namespace Digidirect\Blog\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * @since 2.4.2
 */
class SeoUrlHelper extends AbstractHelper
{
    /**
     * @param string $url
     * @param string $what
     * @param string $to
     * @param null $count
     * @return mixed
     */
    public function normalizeHtmlUrl(string $url = '', string $what = '.html/', string $to = '.html', $count = null)
    {
        return str_replace($what, $to, $url, $count);
    }
}
