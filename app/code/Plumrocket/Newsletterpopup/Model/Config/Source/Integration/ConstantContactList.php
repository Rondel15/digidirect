<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Config\Source\Integration;

class ConstantContactList extends \Plumrocket\Newsletterpopup\Model\Config\Source\Integration\AbstractIntegrationList
{
    /**
     * @var \Plumrocket\Newsletterpopup\Model\Integration\ConstantContact
     */
    private $integrationModel;

    /**
     * ConstantContact constructor.
     * @param \Plumrocket\Newsletterpopup\Model\Integration\ConstantContact $integrationModel
     */
    public function __construct(
        \Plumrocket\Newsletterpopup\Model\Integration\ConstantContact $integrationModel
    ) {
        $this->integrationModel = $integrationModel;
    }

    /**
     * @return \Plumrocket\Newsletterpopup\Model\Integration\ConstantContact
     */
    public function getModel()
    {
        return $this->integrationModel;
    }
}
