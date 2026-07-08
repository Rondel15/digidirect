<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Config\Source;

class Template extends Base
{
    public function toOptionHash()
    {
        $templates = [
            '' => __(' - '),
        ];

        $items = $this->_template->getCollection();
        foreach ($items as $item) {
            $templates[ $item->getId() ] = $item->getName();
        }
        return $templates;
    }
}
