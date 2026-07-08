<?php
namespace Digidirect\CheckoutFields\Cron;

use Digidirect\CheckoutFields\Api\Data\CronActionInterface;
use \Psr\Log\LoggerInterface;

/**
 * Class Actions
 * @package Digidirect\CheckoutFields\Cron
 */
class Actions
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CronActionInterface[]
     */
    protected $actions;

    /**
     * Actions constructor.
     * @param LoggerInterface $logger
     * @param array $actions
     */
    public function __construct(
        LoggerInterface $logger,
        array $actions = []
    ) {
        $this->logger = $logger;
        $this->actions = $actions;
    }

    /**
     * @return void
     */
    public function execute()
    {
        if (empty($this->actions)) {
            return;
        }

        foreach ($this->actions as $key => $action) {
            if (!$action instanceof CronActionInterface) {
                $this->logger->warning('An Action for Checkout_Fields Cron is not valid', ['key' => $key]);
                continue;
            }
            try {
                $action->runAction();
            } catch (\Throwable $exception) {
                $this->logActionError($action, $exception);
            }
        }
    }

    /**
     * @param CronActionInterface $action
     * @param \Throwable $exception
     * @return void
     */
    protected function logActionError(CronActionInterface $action, \Throwable $exception)
    {
        $this->logger->error(
            __(
                "An error occurred during Checkout_Fields Cron Action %1 execution. Message: %2",
                $action->getName(),
                $exception->getMessage()
            )
        );
    }
}
