<?php

namespace Digidirect\Vii\Service\Response\PreAuth;

use Digidirect\AbstractGiftCard\Service\Helper\SubjectReader;
use Digidirect\AbstractGiftCard\Service\Response\HandlerInterface;
use Digidirect\Vii\Service\Response\CheckStatus\Handler as CheckStatusHandle;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\DataObject;
use Digidirect\AbstractGiftCard\Model\AbstractGiftCardEntity;

/**
 * Class Handler
 * @package Digidirect\Vii\Service\Response\PreAuth
 */
class Handler implements HandlerInterface
{
    const PRE_AUTH_CODE = 'PreAuthCode';
    const AMOUNT = 'Amount';

    /**
     * @var \Digidirect\AbstractGiftCard\Service\Helper\GiftCardGenerator
     */
    protected $_cardGenerator;

    /**
     * @var \Digidirect\Vii\Api\AbstractGiftCardEntityRepositoryInterface
     */
    protected $abstractGiftCardEntityRepository;

    /**
     * @var \Magento\GiftCardAccount\Model\GiftcardaccountFactory
     */
    protected $giftCardAccountFactory;

    /**
     * @var \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityRepositoryInterface
     */
    protected $baseGiftCardEntityRepository;

    /**
     * Handler constructor.
     * @param \Digidirect\AbstractGiftCard\Service\Helper\GiftCardGenerator $cardGenerator
     * @param \Digidirect\Vii\Api\AbstractGiftCardEntityRepositoryInterface $abstractGiftCardEntityRepository
     * @param \Magento\GiftCardAccount\Model\GiftcardaccountFactory $giftcardaccountFactory
     * @param \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityRepositoryInterface $baseGiftCardEntityRepository
     */
    public function __construct(
        \Digidirect\AbstractGiftCard\Service\Helper\GiftCardGenerator $cardGenerator,
        \Digidirect\Vii\Api\AbstractGiftCardEntityRepositoryInterface $abstractGiftCardEntityRepository,
        \Magento\GiftCardAccount\Model\GiftcardaccountFactory $giftcardaccountFactory,
        \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityRepositoryInterface $baseGiftCardEntityRepository
    ) {
        $this->_cardGenerator = $cardGenerator;
        $this->abstractGiftCardEntityRepository = $abstractGiftCardEntityRepository;
        $this->giftCardAccountFactory = $giftcardaccountFactory;
        $this->baseGiftCardEntityRepository = $baseGiftCardEntityRepository;
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
        $dateExpires = \DateTime::createFromFormat(
            CheckStatusHandle::EXPIRATION_DATE_FORMAT,
            $checkResponse[CheckStatusHandle::EXPIRY_DATE]
        )->format(DateTime::DATETIME_PHP_FORMAT);

        $giftCardData = new DataObject([
            'code' => $checkResponse[CheckStatusHandle::CODE],
            'pin'  => $serviceDO->getService()->getAbstractGiftCardEntity()->getData(CheckStatusHandle::PIN),
            'balance' => $checkResponse[self::AMOUNT],
            'available_amount' => $checkResponse["CurrentBalance"],
            'date_expires' => $dateExpires,
            'card_status' => $checkResponse[CheckStatusHandle::CARD_STATUS_ID]
        ]);

        $giftCardAccount = $this->_cardGenerator->initGiftCardAccount($serviceDO->getService(), $giftCardData);
        $entity = $this->baseGiftCardEntityRepository->loadByGiftCardAccount($giftCardAccount);

        $entityQuoteData = new \Magento\Framework\DataObject(['status' => AbstractGiftCardEntity::STATUS_HOLD]);
        $entityQuoteData->setQuoteId($serviceDO->getQuote()->getId());
        $entityQuoteData->setAmount($checkResponse[self::AMOUNT]);
        $entityQuoteData->setToken($checkResponse[self::PRE_AUTH_CODE]);
        $entityQuoteData->setAbstractGiftCardEntityId($entity->getEntityId());
        $entityQuoteData->setAvailableAmount($checkResponse["CurrentBalance"]);
        $entityQuoteData->setPin($serviceDO->getService()->getAbstractGiftCardEntity()->getData(CheckStatusHandle::PIN));
        $this->abstractGiftCardEntityRepository->saveEntityQuoteData($entityQuoteData);
        $serviceDO->getService()->setLastToken($checkResponse[self::PRE_AUTH_CODE]);
        return $this;
    }
}
