<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Config\Source\Integration;

class DotmailerList extends \Plumrocket\Newsletterpopup\Model\Config\Source\Integration\AbstractIntegrationList
{
    /**
     * @var \Plumrocket\Newsletterpopup\Model\Integration\Dotmailer
     */
    private $integrationModel;

    /**
     * ActiveCampaignList constructor.
     * @param \Plumrocket\Newsletterpopup\Model\Integration\Dotmailer $integrationModel
     */
    public function __construct(
        \Plumrocket\Newsletterpopup\Model\Integration\Dotmailer $integrationModel
    ) {
        $this->integrationModel = $integrationModel;
    }

    /**
     * @return \Plumrocket\Newsletterpopup\Model\Integration\Dotmailer
     */
    public function getModel()
    {
        return $this->integrationModel;
    }
}
