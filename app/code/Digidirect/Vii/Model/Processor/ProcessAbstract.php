<?php

namespace Digidirect\Vii\Model\Processor;

use \Digidirect\AI\Model\Engine\Processor\ProcessorAbstract;
use Digidirect\AI\Model\Engine\Processor\Exception\ProcessException;
use Digidirect\AI\Model\Logger\Logger;

/**
 * Class ProcessAbstract
 * @package Digidirect\Vii\Model\Processor
 * @method \Digidirect\AI\Model\Logger\Logger getLogger()
 */
class ProcessAbstract extends ProcessorAbstract
{
    /**
     * @var bool
     */
    protected $useQueue = true;

    /**
     * @var \Magento\GiftCardAccount\Helper\Data
     */
    protected $giftCAHelper;

    /**
     * @var \Magento\GiftCardAccount\Model\GiftcardaccountFactory $giftcardaccountFactory
     */
    protected $giftCardAccountFactory;

    /**
     * @var \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityRepositoryInterface
     */
    protected $abstractGiftCardEntityRepository;

    /**
     * @var \Digidirect\Vii\Api\AbstractGiftCardEntityRepositoryInterface
     */
    protected $viiGiftCardEntityRepository;

    /**
     * ProcessAbstract constructor.
     * @param \Magento\GiftCardAccount\Helper\Data $giftCAHelper
     * @param \Magento\GiftCardAccount\Model\GiftcardaccountFactory $giftcardaccountFactory
     * @param \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityRepositoryInterface $abstractGiftCardEntityRepository
     * @param \Digidirect\Vii\Api\AbstractGiftCardEntityRepositoryInterface $viiGiftCardEntityRepository
     * @param null $initParams
     */
    public function __construct(
        \Magento\GiftCardAccount\Helper\Data $giftCAHelper,
        \Magento\GiftCardAccount\Model\GiftcardaccountFactory $giftcardaccountFactory,
        \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityRepositoryInterface $abstractGiftCardEntityRepository,
        \Digidirect\Vii\Api\AbstractGiftCardEntityRepositoryInterface $viiGiftCardEntityRepository,
        $initParams = null
    ) {
        parent::__construct($initParams);
        $this->giftCAHelper = $giftCAHelper;
        $this->giftCardAccountFactory = $giftcardaccountFactory;
        $this->abstractGiftCardEntityRepository = $abstractGiftCardEntityRepository;
        $this->viiGiftCardEntityRepository = $viiGiftCardEntityRepository;
    }

    /**
     * @param string $message
     * @param null $e
     * @param int $stopper
     * @throws ProcessException
     * @throws \Digidirect\AI\Model\Engine\Exception\EngineException
     * @return void
     */
    protected function throwException($message, $e = null, $stopper = 0)
    {
        $this->getLogger()->critical($message, [], Logger::LOG_PLACE_FILE_AND_DB);
        if ($this->useQueue && !$this->isAdminRun && !$this->getQueueId()) {
            $this->setFlag(ProcessorAbstract::FLAG_PUT_TU_QUEUE, 1);
        }
        $errorCode = ($e instanceof \Throwable) ? $e->getCode() : 0;
        throw new ProcessException($message, $errorCode, $e, $stopper);
    }
}
