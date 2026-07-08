<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\Popups\Edit\Tabs\Integration;

/**
 * Class Dotmailer
 */
class Dotmailer extends \Plumrocket\Newsletterpopup\Block\Adminhtml\Popups\Edit\Tabs\AbstractIntegration
{
    /**
     * @return string
     */
    public function getIntegrationId()
    {
        return \Plumrocket\Newsletterpopup\Model\Integration\Dotmailer::INTEGRATION_ID;
    }

    /**
     * @return string
     */
    public function getIntegrationTitle()
    {
        return __('Dotdigital');
    }
}
