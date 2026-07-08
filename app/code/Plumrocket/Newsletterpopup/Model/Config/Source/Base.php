<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Config\Source;

use Magento\Cms\Model\Page;
use Magento\Framework\Option\ArrayInterface;
use Plumrocket\Newsletterpopup\Helper\Adminhtml;
use Plumrocket\Newsletterpopup\Model\Template;

class Base implements ArrayInterface
{
    protected $_adminhtmlHelper;
    protected $_template;
    protected $_page;

    public function __construct(
        Adminhtml $adminhtmlHelper,
        Template $template,
        Page $page
    ) {
        $this->_adminhtmlHelper = $adminhtmlHelper;
        $this->_template = $template;
        $this->_page = $page;
    }

    public function toOptionArray()
    {
        $values = $this->toOptionHash();
        $result = [];

        foreach ($values as $key => $value) {
            $result[] = [
                'value'    => $key,
                'label'    => $value,
            ];
        }
        return $result;
    }
}
