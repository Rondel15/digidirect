<?php

namespace Digidirect\Vii\Service\Response\Redemption;

use Digidirect\AbstractGiftCard\Service\Helper\SubjectReader;
use Digidirect\AbstractGiftCard\Service\Response\HandlerInterface;
use Digidirect\Vii\Model\ResourceModel\AbstractGiftCardEntity;
use Magento\Sales\Api\Data\OrderInterface;
use Digidirect\AbstractGiftCard\Model\AbstractGiftCardEntity as BaseAbstractGiftCardEntity;

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
        $checkResponse = $serviceDO->getCheckResponse();
        $service = $serviceDO->getService();
        $entity = $service->getAbstractGiftCardEntity();
        $object = $service->getOrder() ?? ($service->getQuote() ?? $serviceDO->getQuote());
        $quoteId = ($object instanceof OrderInterface) ? $object->getQuoteId() : $object->getId();
        $this->abstractGiftCardEntity->updateEntityQuoteData(
            ['status' => BaseAbstractGiftCardEntity::STATUS_ACCEPT],
            [
                'quote_id = ?' => $quoteId,
                'abstract_gift_card_entity_id = ?' => $entity->getEntityId()
            ]
        );
        return $this;
    }
}
