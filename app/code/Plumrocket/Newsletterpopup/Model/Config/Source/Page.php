<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Config\Source;

class Page extends Base
{
    protected $_options = null;

    public function toOptionHash()
    {
        if (null === $this->_options) {
            $collection = $this->_page->getCollection()
                ->addFieldToFilter('is_active', 1)
                ->setOrder('sort_order', 'asc');

            $options = [];
            foreach ($collection as $item) {
                $options[ $item->getIdentifier() ] = $item->getTitle();
            }

            $this->_options = $options;
        }

        return $this->_options;
    }
}
