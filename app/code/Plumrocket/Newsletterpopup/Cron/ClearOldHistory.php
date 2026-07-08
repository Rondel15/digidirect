<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Cron;

use Magento\Framework\App\ResourceConnection;
use Plumrocket\Newsletterpopup\Helper\Config;
use Plumrocket\Newsletterpopup\Helper\DateTime;

class ClearOldHistory
{

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Plumrocket\Newsletterpopup\Helper\DateTime
     */
    private $dateTime;

    /**
     * @var \Plumrocket\Newsletterpopup\Helper\Config
     */
    private $config;

    /**
     * @param \Magento\Framework\App\ResourceConnection   $resourceConnection
     * @param \Plumrocket\Newsletterpopup\Helper\DateTime $dateTime
     * @param \Plumrocket\Newsletterpopup\Helper\Config   $config
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        DateTime $dateTime,
        Config $config
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->dateTime = $dateTime;
        $this->config = $config;
    }

    /**
     * Disable old history.
     *
     * @return void
     */
    public function execute(): void
    {
        if ($this->config->isModuleEnabled() && $this->config->isHistoryEnabled()) {
            // count of months
            $offset = $this->config->getHistoryExpiration() * 86400;
            // if 0 then never erase
            if ($offset) {
                $this->resourceConnection->getConnection('write')
                    ->query(sprintf(
                        "DELETE FROM %s WHERE `date_created` <= '%s'",
                        $this->resourceConnection->getTableName('plumrocket_newsletterpopup_history'),
                        $this->dateTime->format(time() - $offset, 'YYYY-MM-dd hh:mm:ss')
                    ));
            }
        }
    }
}
