<?php
namespace Digidirect\CollectAbstractEntity\Model\ResourceModel\Order\Shipment;

/**
 * Class Track
 * @package Digidirect\CollectAbstractEntity\Model\ResourceModel\Order\Shipment
 */
class Track extends \Magento\Sales\Model\ResourceModel\Order\Shipment\Track
{
    const EMAIL_SENT = 'email_sent';

    /**
     * @param \Magento\Sales\Model\Order\Shipment\Track $track
     * @param bool $flag
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setTrackingNumberEmailSentFlag(
        \Magento\Sales\Model\Order\Shipment\Track $track,
        bool $flag
    ) {
        $this->saveAttribute(
            $track->setEmailSent($flag),
            self::EMAIL_SENT
        );
    }
}
