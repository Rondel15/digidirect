<?php

namespace Digidirect\Vii\Service\Response\PreAuthCancellation;

use Digidirect\AbstractGiftCard\Service\Helper\SubjectReader;
use Digidirect\AbstractGiftCard\Service\Response\HandlerInterface;
use Digidirect\Vii\Model\ResourceModel\AbstractGiftCardEntity;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class Handler
 * @package Digidirect\Vii\Service\Response\PreAuthCancellation
 */
class Handler implements HandlerInterface
{
    /**
     * @var AbstractGiftCardEntity
     */
    protected $abstractGiftCardEntity;

    /**
     * Handler constructor.
     * @param AbstractGiftCardEntity $abstractGiftCardEntity
     */
    public function __construct(AbstractGiftCardEntity $abstractGiftCardEntity)
    {
        $this->abstractGiftCardEntity = $abstractGiftCardEntity;
    }

    /**
     * @param array $handlingSubject
     * @param array $response
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(array $handlingSubject, array $response)
    {
        $serviceDO = SubjectReader::readService($handlingSubject);
        $service = $serviceDO->getService();
        $entity = $service->getAbstractGiftCardEntity();
        $object = $service->getOrder() ?? ($service->getQuote() ?? $serviceDO->getQuote());
        $quoteId = ($object instanceof OrderInterface) ? $object->getQuoteId() : $object->getId();
        $this->abstractGiftCardEntity->deleteEntityQuoteData($entity->getEntityId(), $quoteId);

        return $this;
    }
}
