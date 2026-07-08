<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Config\Source\Integration;

class OntraportList extends \Plumrocket\Newsletterpopup\Model\Config\Source\Integration\AbstractIntegrationList
{
    /**
     * @var \Plumrocket\Newsletterpopup\Model\Integration\Ontraport
     */
    private $integrationModel;

    /**
     * ConvertKit constructor.
     * @param \Plumrocket\Newsletterpopup\Model\Integration\Ontraport $integrationModel
     */
    public function __construct(
        \Plumrocket\Newsletterpopup\Model\Integration\Ontraport $integrationModel
    ) {
        $this->integrationModel = $integrationModel;
    }

    /**
     * @return \Plumrocket\Newsletterpopup\Model\Integration\Ontraport
     */
    public function getModel()
    {
        return $this->integrationModel;
    }
}
