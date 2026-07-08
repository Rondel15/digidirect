<?php

namespace Digidirect\AI\Model\Engine\Queue;

use Digidirect\AI\Api\Data\QueueInterface;
use Digidirect\AI\Helper\Engine as EngineHelper;
use Digidirect\AI\Helper\Queue as QueueHelper;
use Digidirect\AI\Model\Engine\EngineFactory;
use Digidirect\AI\Model\Engine\Exception\QueueDependsException;
use Digidirect\AI\Model\Engine\Queue\QueueFactory;
use Digidirect\AI\Model\Engine\Mail\MailFactory;
use Digidirect\AI\Model\Engine\Queue\State as QueueState;
use Digidirect\AI\Model\Engine\Queue\Status as QueueStatus;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json as Serializer;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;

/**
 * Class QueueProcessor
 *
 * @package Digidirect\AI\Model\Engine\Queue
 */
class QueueProcessor
{
    const CONFIG_NUMBER_OF_ATTEMPTS = 'digidirect_ai/queue/number_of_attempts';
    const CONFIG_NUMBER_OF_ATTEMPTS_PER_PROCESS = 'digidirect_ai/queue/number_of_attempts_per_process';

    /**
     * @var QueueHelper
     */
    protected $queueHelper;

    /**
     * @var QueueRepository
     */
    protected $queueRepository;

    /**
     * @var QueueFactory
     */
    protected $queueFactory;

    /**
     * @var EngineFactory
     */
    protected $engineFactory;

    /**
     * @var MailFactory
     */
    protected $mailerFactory;

    /**
     * @var DateTimeFactory
     */
    protected $dateFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var int
     */
    protected $numberOfAttempts;

    /**
     * @var array
     */
    protected $numberOfAttemptsPerProcess;

    /**
     * QueueProcessor constructor.
     * @param EngineFactory $engineFactory
     * @param MailFactory $mailerFactory
     * @param \Digidirect\AI\Model\Engine\Queue\QueueRepository $queueRepository
     * @param \Digidirect\AI\Model\Engine\Queue\QueueFactory $queueFactory
     * @param QueueHelper $queueHelper
     * @param DateTimeFactory $dateFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param Serializer $serializer
     */
    public function __construct(
        EngineFactory $engineFactory,
        MailFactory $mailerFactory,
        QueueRepository $queueRepository,
        QueueFactory $queueFactory,
        QueueHelper $queueHelper,
        DateTimeFactory $dateFactory,
        ScopeConfigInterface $scopeConfig,
        Serializer $serializer
    ) {
        $this->engineFactory = $engineFactory;
        $this->mailerFactory = $mailerFactory;
        $this->queueRepository = $queueRepository;
        $this->queueFactory = $queueFactory;
        $this->queueHelper = $queueHelper;
        $this->dateFactory = $dateFactory;
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer;
        $this->numberOfAttempts = (int)$this->scopeConfig->getValue(self::CONFIG_NUMBER_OF_ATTEMPTS);
        $this->numberOfAttemptsPerProcess = $this->getNumberOfAttemptsByProcessCode();
    }

    /**
     * @return array
     */
    protected function getNumberOfAttemptsByProcessCode()
    {
        $result = [];
        $configValue = $this->scopeConfig->getValue(self::CONFIG_NUMBER_OF_ATTEMPTS_PER_PROCESS);
        $configValue = $configValue ? $this->serializer->unserialize($configValue) : [];
        foreach ($configValue as $item) {
            if (!isset($item['label_column']) || !isset($item['value_column'])) {
                continue;
            }
            $label = $item['label_column'];
            $value = (int)$item['value_column'];

            $result[$label] = $value;
        }
        return $result;
    }

    /**
     * for backward compatibility: created for an ability to create plugin for old modules.
     *
     * @param QueueInterface $queueItem
     * @return array
     */
    public function getUnserializedProcessDataFromQueue($queueItem)
    {
        return $this->queueHelper->getUnserializedProcessData($queueItem->getProcessData());
    }

    /**
     * Process Queue Item
     *
     * @param QueueInterface $queueItem
     * @return $this
     */
    public function processQueueItem(QueueInterface $queueItem)
    {
        //mark state as processing
        $queueItem->setState(QueueState::STATE_PROCESSING);
        $queueItem->setStatus(QueueStatus::STATUS_PROCESSING);

        //put to repository
        $this->queueRepository->save($queueItem);

        try {
            /** @var \Digidirect\AI\Model\Engine\Engine $integration */
            $runOptions = $this->getUnserializedProcessDataFromQueue($queueItem);
            $integration = $this->engineFactory
                ->create(
                    [
                        'processCode' => $queueItem->getProcessCode(),
                        'initiator' => 'CRON: Queue Run: ' . $queueItem->getInitiator(),
                        'queueId' => $queueItem->getId(),
                        'runOptions' => $runOptions
                    ]
                );

            $resultData = $integration->run();
            $exception = $resultData->getException();
            if ($exception instanceof QueueDependsException) {
                throw $exception;
            }

            //update from repository
            $queueItem = $this->queueRepository->get($queueItem->getId(), true);
            $queueItem->setRunNumber($queueItem->getRunNumber() + 1);

            $maxAttempts = $this->numberOfAttemptsPerProcess[$queueItem->getProcessCode()] ?? $this->numberOfAttempts;
            if ($resultData->getResult() || $queueItem->getRunNumber() >= $maxAttempts) {
                //mark as complete
                $queueItem->setState(QueueState::STATE_CLOSED);
            } else {
                //mark as incomplete and put to end of queue
                $queueItem->setState(QueueState::STATE_PENDING);
                $queueItem->setSequence($this->queueRepository->getNextSequenceId());
            }

            // if state is closed then need change status
            if ($queueItem->getState() == QueueState::STATE_CLOSED) {
                $queueItem->setStatus(
                    $resultData->getResult() ? QueueStatus::STATUS_SUCCESS : QueueStatus::STATUS_FAILED
                );
            }

            // if state is closed and status success need mark as retired
            if ($queueItem->getState() == QueueState::STATE_CLOSED
                && $queueItem->getStatus() == QueueStatus::STATUS_SUCCESS
            ) {
                $queueItem->setIsRetired(true);
            }
        } catch (QueueDependsException $e) {
            //item not ready for now - need put to end of queue
            $queueItem->setState(QueueState::STATE_PENDING_DEPENDS);
            $queueItem->setStatus(QueueStatus::STATUS_PENDING);
            $queueItem->setSequence($this->queueRepository->getNextSequenceId());
        } finally {
            $queueItem->setLastRunAt($this->dateFactory->create()->gmtDate());
        }

        //update in repository
        $this->queueRepository->save($queueItem);

        if ($queueItem->getStatus() == QueueStatus::STATUS_FAILED) {
            /**
             * @var $mailer \Digidirect\AI\Model\Engine\Mail\Mail
             */
            $mailer = $this->mailerFactory->create();
            $mailer->sendQueueFailEmail($queueItem, $runOptions);
        }

        return $this;
    }
}
