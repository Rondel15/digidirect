<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Source\Email;

use Magento\Config\Model\Config\Source\Email\Template as EmailTemplate;

class Template extends EmailTemplate
{
    public function getPath()
    {
        return 'prnewsletterpopup/general/email/template';
    }
}
