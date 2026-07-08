<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model;

/**
 * Interface IntegrationIdentifierListInterface
 */
interface IntegrationIdentifierListInterface
{
    /**
     * Check if integration identifier is valid
     *
     * @param string $integrationIdentifier
     * @return bool
     */
    public function isValid($integrationIdentifier);

    /**
     * Retrieve class name of integration
     *
     * @param string $integrationIdentifier
     * @return false|string
     */
    public function getIntegrationClass($integrationIdentifier);

    /**
     * Retrieve array of integration identifiers
     *
     * @return array
     */
    public function getIntegrationIdentifiers();
}
