<?php

namespace Digidirect\Vii\Service\Response\CheckStatus;

use Magento\Framework\DataObject;
use Digidirect\AbstractGiftCard\Service\Helper\SubjectReader;
use Digidirect\AbstractGiftCard\Service\Response\HandlerInterface;
use Magento\Framework\Stdlib\DateTime;

/**
 * Class Handler
 * @package Digidirect\Vii\Service\Response\CheckStatus
 */
class Handler implements HandlerInterface
{
    const CODE = 'CardNumber';
    const BALANCE = 'AvailableBalance';
    const PIN = 'pin';
    const EXPIRY_DATE = 'ExpiryDate';
    const CARD_STATUS_ID = 'CardStatusId';
    const EXPIRATION_DATE_FORMAT = 'd/m/Y H:i:s A';

    /**
     * @var \Digidirect\AbstractGiftCard\Service\Helper\GiftCardGenerator
     */
    protected $_cardGenerator;

    /**
     * Handler constructor.
     * @param \Digidirect\AbstractGiftCard\Service\Helper\GiftCardGenerator $cardGenerator
     */
    public function __construct(\Digidirect\AbstractGiftCard\Service\Helper\GiftCardGenerator $cardGenerator)
    {
        $this->_cardGenerator = $cardGenerator;
    }

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $serviceDO = SubjectReader::readService($handlingSubject);
        $checkResponse = $serviceDO->getCheckResponse();
        $dateExpires = \DateTime::createFromFormat(
            self::EXPIRATION_DATE_FORMAT,
            $checkResponse[self::EXPIRY_DATE]
        )->format(DateTime::DATETIME_PHP_FORMAT);

        $giftCardData = new DataObject([
            'code' => $checkResponse[self::CODE],
            'pin'  => $serviceDO->getService()->getAbstractGiftCardEntity()->getData(self::PIN),
            'balance' => $checkResponse[self::BALANCE],
            'date_expires' => $dateExpires,
            'card_status' => $checkResponse[self::CARD_STATUS_ID]
        ]);

        $this->_cardGenerator->initGiftCardAccount($serviceDO->getService(), $giftCardData);

        return $this;
    }
}
