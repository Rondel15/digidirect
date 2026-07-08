<?php
namespace Digidirect\InfiniteScroll\Model\Config;

class Data extends \Magento\Framework\Config\Data
{
    /**
     * @param string $handle
     * @return bool
     */
    public function getScrollForHandle($handle)
    {
        $scrolls = $this->get('infinitescrolls');

        if (isset($scrolls[$handle])) {
            return $scrolls[$handle];
        }
        return false;
    }
}
