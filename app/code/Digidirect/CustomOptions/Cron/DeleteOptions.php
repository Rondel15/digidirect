<?php
namespace Digidirect\CustomOptions\Cron;

use Psr\Log\LoggerInterface;
use Digidirect\CustomOptions\Helper\CronCustomOption;

class DeleteOptions
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CronCustomOption
     */
    protected $helper;

    public function __construct(
            LoggerInterface $logger,
            CronCustomOption $helper)
    {
        $this->logger = $logger;
        $this->helper = $helper;
    }

    public function execute()
    {
        exit;
        $this->helper->deleteCustomOption();

    }
}
