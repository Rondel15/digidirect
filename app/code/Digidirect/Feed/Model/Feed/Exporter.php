<?php

namespace Digidirect\Feed\Model\Feed;

use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Stdlib\DateTime;
use Magento\Store\Model\App\Emulation;
use Digidirect\Feed\Export\Handler;
use Digidirect\Feed\Export\HandlerFactory;
use Digidirect\Feed\Model\Config;
use Digidirect\Feed\Model\Feed;
use Digidirect\Feed\Model\FeedRepository;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Exporter
{
    /**
     * @var Emulation
     */
    protected $appEmulation;

    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * @var HandlerFactory
     */
    protected $handlerFactory;

    /**
     * @var History
     */
    protected $history;

    /**
     * Email Notifier
     *
     * @var Notifier
     */
    protected $notifier;

    /**
     * @var FeedRepository
     */
    protected $feedRepository;

    /**
     * @var array
     */
    protected $handlers = [];

    /**
     * Exporter constructor.
     * @param Emulation $appEmulation
     * @param EventManager $eventManager
     * @param HandlerFactory $handlerFactory
     * @param History $history
     * @param Notifier $notifier
     * @param FeedRepository $feedRepository
     */
    public function __construct(
        Emulation $appEmulation,
        EventManager $eventManager,
        HandlerFactory $handlerFactory,
        History $history,
        Notifier $notifier,
        FeedRepository $feedRepository
    ) {
        $this->appEmulation = $appEmulation;
        $this->eventManager = $eventManager;
        $this->handlerFactory = $handlerFactory;
        $this->history = $history;
        $this->notifier = $notifier;
        $this->feedRepository = $feedRepository;
    }

    /**
     * Export Handler
     *
     * @param Feed $feed
     * @return Handler
     */
    public function getHandler(Feed $feed)
    {
        if (!isset($this->handlers[$feed->getId()])) {
            $this->handlers[$feed->getId()] = $this->handlerFactory->create()
                ->setFeed($feed);
        }

        return $this->handlers[$feed->getId()];
    }

    /**
     * Export feed via browser (few iterations)
     *
     * @param Feed $feed
     * @return string
     * @throws \Exception
     */
    public function export(Feed $feed)
    {
        register_shutdown_function([$this, 'onShutdown'], $feed);

        $this->appEmulation->startEnvironmentEmulation($feed->getStore()->getId());

        $handler = $this->getHandler($feed);
        $handler->setFilename($feed->getFilename());

        try {
            $handler->execute();

            $this->updateFeed($feed, $handler);
        } catch (\Exception $e) {
            $this->notifier->exportFail($feed, $e->getMessage());
            throw $e;
        }

        $this->appEmulation->stopEnvironmentEmulation();

        return $handler->getStatus();
    }

    /**
     * Export feed in shell
     *
     * @param Feed $feed
     * @return void
     * @throws \Exception
     */
    public function exportCli(Feed $feed)
    {
        register_shutdown_function([$this, 'onShutdown'], $feed);

        $this->history->add($feed, __('Export'), __('Start export process'));

        $this->appEmulation->startEnvironmentEmulation($feed->getStore()->getId());

        $handler = $this->getHandler($feed);

        $handler->reset()
            ->setFilename($feed->getFilename());

        try {
            do {
                $handler->execute();

                $this->updateFeed($feed, $handler);

                yield $handler->getStatus() => $handler->toString();
            } while (!in_array($handler->getStatus(), [
                Config::STATUS_COMPLETED,
                Config::STATUS_ERROR,
            ]));
        } catch (\Exception $e) {
            $this->notifier->exportFail($feed, $e->getMessage());
            throw $e;
        }

        $this->appEmulation->stopEnvironmentEmulation();
    }

    /**
     * @param Feed $feed
     * @return array
     * @throws \Exception
     */
    public function exportByCron(Feed $feed)
    {
        $result = [];
        foreach ($this->exportCli($feed) as $status => $message) {
            $result[] = ['status' => $status, 'message' => $message];
        }
        return $result;
    }

    /**
     * Export feed preview (first 10 products)
     *
     * @param Feed $feed
     * @return void
     */
    public function exportPreview(Feed $feed)
    {
        $appEmulation = $this->appEmulation;
        $appEmulation->startEnvironmentEmulation($feed->getStore()->getId());

        $handler = $this->getHandler($feed);

        $handler->reset();

        $handler->setFilename($feed->getPreviewFilename())
            ->enableTestMode();

        do {
            $handler->execute();
        } while (!in_array($handler->getStatus(), [
            Config::STATUS_COMPLETED,
            Config::STATUS_ERROR,
        ]));

        $appEmulation->stopEnvironmentEmulation();
    }

    /**
     * Update export information for feed
     *
     * @param Feed $feed
     * @param Handler $handler
     * @return $this
     * @throws \Exception
     */
    protected function updateFeed(Feed $feed, Handler $handler)
    {
        $this->history->add($feed, __('Export'), $handler->toString());

        if ($handler->getStatus() == Config::STATUS_COMPLETED) {
            $feed->setGeneratedAt((new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT))
                ->setGeneratedTime($handler->getTimeSinceStart());

            $this->feedRepository->save($feed);

            $this->history->add($feed, __('Export'), __('Feed was successfully exported'));
            $this->notifier->exportSuccess($feed);
        }

        return $this;
    }

    /**
     * Save fatal errors to feed history
     *
     * @param Feed $feed
     * @return void
     */
    public function onShutdown($feed)
    {
        if (error_get_last()) {
            $error = error_get_last();
            if ($error['type'] === E_ERROR) {
                $message = $error['message'];
                $this->history->add($feed, __('Error'), $message);
            }
        }
    }
}
