<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Integration;

class Logger extends \Monolog\Logger
{
    /**
     * Name of integration instance
     *
     * @var string
     */
    private $integrationName = 'Integration';

    /**
     * Set name for Integration
     *
     * @param string $name
     * @return $this
     */
    public function setIntegrationName($name)
    {
        $name = trim($name);

        if (! empty($name)) {
            $this->integrationName = $name;
        }

        return $this;
    }

    /**
     * Get Integration Name
     *
     * @return string
     */
    public function getIntegrationName(): string
    {
        return $this->integrationName;
    }
}
