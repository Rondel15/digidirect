<?php
namespace Digidirect\CollectAbstractEntity\Observer;

use Digidirect\CollectAbstractEntity\Helper\Config as ConfigHelper;
use Digidirect\CollectAbstractEntity\Model\ResourceModel\Order\Shipment\TrackFactory;
use Digidirect\Collect\Model\Carrier\Collectcarrier;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Mail\Template\TransportBuilder;

/**
 * Class SendTrackingNumberEmailNotification
 * @package Digidirect\CollectAbstractEntity\Observer
 */
class SendTrackingNumberEmailNotification implements ObserverInterface
{
    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var TrackFactory
     */
    protected $trackFactory;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * SendTrackingNumberEmailNotification constructor.
     * @param TransportBuilder $transportBuilder
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        TrackFactory $trackFactory,
        ConfigHelper $configHelper
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->trackFactory = $trackFactory;
        $this->configHelper = $configHelper;
    }

    /**
     * Send email with "Tracking Number" by event "sales_order_shipment_track_process_relation"
     *
     * @param Observer $observer
     * @return bool|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     */
    public function execute(Observer $observer)
    {
        $track = $observer->getEvent()->getObject();
        $shipment = $track->getShipment();
        $order = $shipment->getOrder();
        if (!$this->configHelper->isTrackingNumberAddingNotificationEnabled()
            || $order->getShippingMethod() != Collectcarrier::COLLECT_SHIPPING_METHOD
            || $track->getEmailSent()) {
            return false;
        }

        $templateId = $this->configHelper->getTrackingNumberAddingNotificationTemplateIfEnabled();
        if (empty($templateId)) {
            return false;
        }

        $emailTo = $this->configHelper->getEmailForTrackingNumberAddingNotification($order);
        if (empty($emailTo)) {
            return false;
        }

        $templateVars = [
            'customer_name'   => $order->getShippingAddress()->getName(),
            'order_number'    => $order->getIncrementId(),
            'tracking_number' => $track->getTrackNumber()
        ];

        $transport = $this->transportBuilder
            ->setTemplateIdentifier($templateId)
            ->setTemplateOptions(
                [
                    'area'  => \Magento\Framework\App\Area::AREA_ADMINHTML,
                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                ]
            )
            ->setTemplateVars($templateVars)
            ->setFrom('sales')
            ->addTo($emailTo)
            ->getTransport();

        $transport->sendMessage();

        $this->trackFactory->create()->setTrackingNumberEmailSentFlag($track, true);
    }
}
