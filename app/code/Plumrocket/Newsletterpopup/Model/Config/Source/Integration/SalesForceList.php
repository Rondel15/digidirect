<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Config\Source\Integration;

class SalesForceList extends \Plumrocket\Newsletterpopup\Model\Config\Source\Integration\AbstractIntegrationList
{
    /**
     * @var \Plumrocket\Newsletterpopup\Model\Integration\SalesForceList
     */
    private $integrationModel;

    /**
     * CampaignMonitorList constructor.
     * @param \Plumrocket\Newsletterpopup\Model\Integration\SalesForce $integrationModel
     */
    public function __construct(
        \Plumrocket\Newsletterpopup\Model\Integration\SalesForce $integrationModel
    ) {
        $this->integrationModel = $integrationModel;
    }

    /**
     * @return \Plumrocket\Newsletterpopup\Model\Integration\SalesForceList
     */
    public function getModel()
    {
        return $this->integrationModel;
    }
}
