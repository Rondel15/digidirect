<?php

namespace Digidirect\Vii\Cron;

use Digidirect\AI\Model\Engine\EngineFactory;
use Digidirect\Vii\Model\ResourceModel\UndoQueue;
use Magento\Framework\Exception\LocalizedException;

class SendUndo
{
    /**
     * @var EngineFactory
     */
    protected $engineFactory;

    /**
     * @var UndoQueue
     */
    protected $undoQueueResource;

    /**
     * SendUndo constructor.
     * @param EngineFactory $engineFactory
     * @param UndoQueue $undoQueueResource
     */
    public function __construct(
        EngineFactory $engineFactory,
        UndoQueue $undoQueueResource
    ) {
        $this->engineFactory = $engineFactory;
        $this->undoQueueResource = $undoQueueResource;
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function execute()
    {
        foreach ($this->undoQueueResource->getAllRecords() as $queueItem) {
            $this->engineFactory->create(
                [
                    'processCode' => \Digidirect\Vii\Model\Processor\UndoProcess::PROCESS_CODE,
                    'initiator' => self::class,
                    'runOptions' => [
                        'id' => $queueItem[UndoQueue::ID],
                        'abstract_gift_card_entity_id' => $queueItem[UndoQueue::ABSTRACT_GIFT_CARD_ENTITY_ID],
                        'quote_id' => $queueItem[UndoQueue::QUOTE_ID],
                        'last_trans_id' => $queueItem[UndoQueue::LAST_TRANS_ID]
                    ]
                ]
            )->run();
        }
    }
}
