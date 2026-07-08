<?php

namespace Digidirect\Feed\Cron;

use Magento\Framework\App\State as AppState;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Digidirect\Feed\Model\Feed;
use Digidirect\Feed\Model\Feed\DelivererFactory;
use Digidirect\Feed\Model\Feed\ExporterFactory;
use Digidirect\Feed\Model\Feed\History;
use Digidirect\Feed\Model\ResourceModel\Feed\Collection as FeedCollection;
use Digidirect\Feed\Model\FeedRepository;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Export
{
    /**
     * @var AppState
     */
    protected $appState;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var FeedCollection
     */
    protected $feedCollection;

    /**
     * @var ExporterFactory
     */
    protected $exporterFactory;

    /**
     * @var DelivererFactory
     */
    protected $delivererFactory;

    /**
     * @var History
     */
    protected $history;

    /**
     * @var FeedRepository
     */
    protected $feedRepository;

    /**
     * Constructor
     *
     * @param AppState $appState
     * @param DateTime $dateTime
     * @param FeedCollection $feedCollection
     * @param ExporterFactory $exporterFactory
     * @param DelivererFactory $delivererFactory
     * @param History $history
     * @param FeedRepository $feedRepository
     */
    public function __construct(
        AppState $appState,
        DateTime $dateTime,
        FeedCollection $feedCollection,
        ExporterFactory $exporterFactory,
        DelivererFactory $delivererFactory,
        History $history,
        FeedRepository $feedRepository
    ) {
        $this->appState = $appState;
        $this->dateTime = $dateTime;
        $this->feedCollection = $feedCollection;
        $this->exporterFactory = $exporterFactory;
        $this->delivererFactory = $delivererFactory;
        $this->history = $history;
        $this->feedRepository = $feedRepository;
    }

    /**
     * Export and delivery feeds
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function execute()
    {
        /**
         * @var Feed $feed
         */
        $collection = $this->feedCollection
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('cron', 1);

        $feedIds = $collection->getAllIds();

        foreach ($feedIds as $feedId) {
            try {
                $feed = $this->feedRepository->getById($feedId);
                if ($this->canExport($feed) >= 0) {
                    $exporter = $this->exporterFactory->create();
                    $deliverer = $this->delivererFactory->create();

                    $exporter->exportByCron($feed);

                    if ($feed->getFtp()) {
                        $deliverer->delivery($feed);
                    }
                } else {
                    $this->history->add($feed, 'Cron', 'Skip cron job.');
                }
            } catch (\Exception $e) {
                $this->history->add($feed, 'Exception', $e->getMessage());
                // @codingStandardsIgnoreStart
                echo $e;
                // @codingStandardsIgnoreEnd
            }
        }
    }

    /**
     * Check conditions for ability to run feed export by cron
     *
     * @param Feed $feed
     * @param int $timestamp
     * @return int
     */
    public function canExport(Feed $feed, $timestamp = null)
    {
        $result = -1;

        $currentDay = (int)$this->dateTime->date('w', $timestamp);
        $currentDayOfYear = (int)$this->dateTime->date('z', $timestamp);
        $currentTime = (int)$this->dateTime->date('G', $timestamp) * 60 + (int)$this->dateTime->date('i', $timestamp);

        $lastRun = strtotime($feed->getGeneratedAt());
        $lastDayOfYear = $this->dateTime->date('z', $lastRun);
        $lastTime = (int)$this->dateTime->date('G', $lastRun) * 60 + (int)$this->dateTime->date('i', $lastRun);
        if (!$feed->getGeneratedAt()) {
            $lastTime = $currentTime - 25;
        }

        // we run generation minimum day ago. Need run generation
        if ($currentDayOfYear > $lastDayOfYear) {
            $lastTime = 0;
        }

        if (in_array($currentDay, $feed->getCronDay())) {
            foreach ($feed->getCronTime() as $cronTime) {
                if ($currentTime >= $cronTime
                    && $cronTime >= $lastTime
                    && $currentTime - $lastTime > 10
                ) {
                    $result = $cronTime;
                    break;
                }
            }
        }

        return $result;
    }
}
