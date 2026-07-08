<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model;

use Plumrocket\Newsletterpopup\Model\Integration\ActiveCampaign;
use Plumrocket\Newsletterpopup\Model\Integration\CampaignMonitor;
use Plumrocket\Newsletterpopup\Model\Integration\ConstantContact;
use Plumrocket\Newsletterpopup\Model\Integration\ConvertKit;
use Plumrocket\Newsletterpopup\Model\Integration\Dotmailer;
use Plumrocket\Newsletterpopup\Model\Integration\Egoi;
use Plumrocket\Newsletterpopup\Model\Integration\Emma;
use Plumrocket\Newsletterpopup\Model\Integration\GetResponse;
use Plumrocket\Newsletterpopup\Model\Integration\HubSpot;
use Plumrocket\Newsletterpopup\Model\Integration\IContact;
use Plumrocket\Newsletterpopup\Model\Integration\InfusionSoft;
use Plumrocket\Newsletterpopup\Model\Integration\Klaviyo;
use Plumrocket\Newsletterpopup\Model\Integration\MadMimi;
use Plumrocket\Newsletterpopup\Model\Integration\Mailjet;
use Plumrocket\Newsletterpopup\Model\Integration\Mautic;
use Plumrocket\Newsletterpopup\Model\Integration\Ontraport;
use Plumrocket\Newsletterpopup\Model\Integration\SalesForce;
use Plumrocket\Newsletterpopup\Model\Integration\SendinBlue;
use Plumrocket\Newsletterpopup\Model\Integration\Sendy;

class IntegrationIdentifierList implements IntegrationIdentifierListInterface
{
    /**
     * @var array
     */
    private $classMap = [
        ActiveCampaign::INTEGRATION_ID  => ActiveCampaign::class,
        HubSpot::INTEGRATION_ID         => HubSpot::class,
        InfusionSoft::INTEGRATION_ID    => InfusionSoft::class,
        Dotmailer::INTEGRATION_ID       => Dotmailer::class,
        Klaviyo::INTEGRATION_ID         => Klaviyo::class,
        GetResponse::INTEGRATION_ID     => GetResponse::class,
        ConstantContact::INTEGRATION_ID => ConstantContact::class,
        CampaignMonitor::INTEGRATION_ID => CampaignMonitor::class,
        ConvertKit::INTEGRATION_ID      => ConvertKit::class,
        Sendy::INTEGRATION_ID           => Sendy::class,
        Ontraport::INTEGRATION_ID       => Ontraport::class,
        Egoi::INTEGRATION_ID            => Egoi::class,
        Mailjet::INTEGRATION_ID         => Mailjet::class,
        SendinBlue::INTEGRATION_ID      => SendinBlue::class,
        IContact::INTEGRATION_ID        => IContact::class,
        MadMimi::INTEGRATION_ID         => MadMimi::class,
        SalesForce::INTEGRATION_ID      => SalesForce::class,
        Mautic::INTEGRATION_ID          => Mautic::class,
        Emma::INTEGRATION_ID            => Emma::class
    ];

    /**
     * Check if integration identifier is valid
     *
     * @param $integrationIdentifier
     * @return bool
     */
    public function isValid($integrationIdentifier)
    {
        return array_key_exists($integrationIdentifier, $this->classMap);
    }

    /**
     * Retrieve class name of integration
     *
     * @param $integrationIdentifier
     * @return false|string
     */
    public function getIntegrationClass($integrationIdentifier)
    {
        return $this->isValid($integrationIdentifier) ? $this->classMap[$integrationIdentifier] : false;
    }

    /**
     * Retrieve array of integration identifiers
     *
     * @return array
     */
    public function getIntegrationIdentifiers()
    {
        return array_keys($this->classMap);
    }
}
