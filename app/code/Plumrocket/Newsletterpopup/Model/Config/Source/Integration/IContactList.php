<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Config\Source\Integration;

class IContactList extends \Plumrocket\Newsletterpopup\Model\Config\Source\Integration\AbstractIntegrationList
{
    /**
     * @var \Plumrocket\Newsletterpopup\Model\Integration\IContact
     */
    private $integrationModel;

    /**
     * ConvertKit constructor.
     * @param \Plumrocket\Newsletterpopup\Model\Integration\IContact $integrationModel
     */
    public function __construct(
        \Plumrocket\Newsletterpopup\Model\Integration\IContact $integrationModel
    ) {
        $this->integrationModel = $integrationModel;
    }

    /**
     * @return \Plumrocket\Newsletterpopup\Model\Integration\IContact
     */
    public function getModel()
    {
        return $this->integrationModel;
    }
}
