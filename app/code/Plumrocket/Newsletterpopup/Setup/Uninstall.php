<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Setup;

use Plumrocket\Base\Setup\AbstractUninstall;
use Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\Theme;

class Uninstall extends AbstractUninstall
{
    protected $_configSectionId = 'prnewsletterpopup';
    protected $_pathes = ['/app/code/Plumrocket/Newsletterpopup'];
    protected $_tables = [
        'plumrocket_newsletterpopup_popups',
        'plumrocket_newsletterpopup_history',
        'plumrocket_newsletterpopup_mailchimp_list',
        'plumrocket_newsletterpopup_form_fields',
        'plumrocket_newsletterpopup_hold',
        Theme::MAIN_TABLE_NAME,
        'plumrocket_newsletterpopup_history_action',
    ];
}
