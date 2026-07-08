<?php
namespace Digidirect\CustomOptions\Cron;

use Psr\Log\LoggerInterface;
use Digidirect\CustomOptions\Helper\CronCustomOption;

class CreateOptions
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

        try {
            $this->helper->saveCustomOption(); //use date today as parameter
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        exit;
    }
}
