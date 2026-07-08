<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model;

use Magento\Framework\Model\AbstractModel;
use Plumrocket\Newsletterpopup\Model\ResourceModel\MailchimpList as MailchimpListResource;

class MailchimpList extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(MailchimpListResource::class);
    }
}
