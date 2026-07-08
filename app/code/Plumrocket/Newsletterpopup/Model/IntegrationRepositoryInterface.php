<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model;

/**
 * Interface IntegrationRepositoryInterface
 */
interface IntegrationRepositoryInterface
{
    /**
     * Retrieve cached object instance
     *
     * @param string $integrationId
     * @return \Plumrocket\Newsletterpopup\Model\IntegrationInterface
     */
    public function get($integrationId);

    /**
     * Get all integrations.
     *
     * @return \Plumrocket\Newsletterpopup\Model\IntegrationInterface[]
     */
    public function getList();
}
