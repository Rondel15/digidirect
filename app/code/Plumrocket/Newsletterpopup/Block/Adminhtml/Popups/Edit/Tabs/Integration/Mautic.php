<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\Popups\Edit\Tabs\Integration;

/**
 * Class Mautic
 */
class Mautic extends \Plumrocket\Newsletterpopup\Block\Adminhtml\Popups\Edit\Tabs\AbstractIntegration
{
    /**
     * @return string
     */
    public function getIntegrationId()
    {
        return \Plumrocket\Newsletterpopup\Model\Integration\Mautic::INTEGRATION_ID;
    }

    /**
     * @return string
     */
    public function getIntegrationTitle()
    {
        return __('Mautic');
    }
}
