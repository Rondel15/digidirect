<?php

namespace Digidirect\Feed\Model\Feed;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Digidirect\Feed\Model\Feed;
use Symfony\Component\Config\Definition\Exception\Exception;

class Notifier
{
    /**
     * Email builder
     *
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * Constructor
     *
     * @param TransportBuilder $transportBuilder
     * @param TimezoneInterface $localeDate
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        TimezoneInterface $localeDate
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->localeDate = $localeDate;
    }

    /**
     * Export Success
     *
     * @param Feed $feed
     * @return bool
     */
    public function exportSuccess(Feed $feed)
    {
        if (!$feed || !in_array('export_success', $feed->getNotificationEvents())) {
            return true;
        }

        $vars = [
            'feed' => $feed,
            'url' => $feed->getUrl(),
            'exported_at' => $this->localeDate->formatDateTime(
                new \DateTime($feed->getGeneratedAt()),
                \IntlDateFormatter::MEDIUM,
                true
            ),
            'export_time' => gmdate('H:i:s', $feed->getGeneratedTime()),
            'export_count' => $feed->getGeneratedCnt(),
        ];

        $this->send($feed, 'feed_export_success', $vars);

        return true;
    }

    /**
     * Export Fail
     *
     * @param Feed $feed
     * @param string $error
     * @return bool
     */
    public function exportFail(Feed $feed, $error)
    {
        if (!$feed || !in_array('export_fail', $feed->getNotificationEvents())) {
            return true;
        }

        $vars = [
            'feed' => $feed,
            'url' => $feed->getUrl(),
            'error' => $error
        ];

        $this->send($feed, 'feed_export_fail', $vars);

        return true;
    }

    /**
     * Delivery Success
     *
     * @param Feed $feed
     * @return bool
     */
    public function deliverySuccess(Feed $feed)
    {
        if (!$feed || !in_array('delivery_success', $feed->getNotificationEvents())) {
            return true;
        }

        $vars = [
            'feed' => $feed,
            'url' => $feed->getUrl(),
            'delivered_at' => $this->localeDate->formatDateTime(
                new \DateTime($feed->getDeliveredAt()),
                \IntlDateFormatter::MEDIUM,
                true
            ),
        ];

        $this->send($feed, 'feed_delivery_success', $vars);

        return true;
    }

    /**
     * Delivery Fail
     *
     * @param Feed $feed
     * @param string $error
     * @return bool
     */
    public function deliveryFail(Feed $feed, $error)
    {
        if (!$feed || !in_array('delivery_fail', $feed->getNotificationEvents())) {
            return true;
        }

        $vars = [
            'feed' => $feed,
            'url' => $feed->getUrl(),
            'error' => $error
        ];

        $this->send($feed, 'feed_delivery_fail', $vars);

        return true;
    }

    /**
     * Send email by given template and vars
     *
     * @param Feed $feed
     * @param string $template
     * @param []     $vars
     * @return bool
     */
    protected function send(Feed $feed, $template, $vars)
    {
        try {
            $notificationEmails = explode(',', $feed->getNotificationEmails());
            foreach ($notificationEmails as $email) {
                if (!trim($email)) {
                    continue;
                }

                $transport = $this->transportBuilder
                    ->setTemplateIdentifier($template)
                    ->setTemplateOptions([
                        'area' => FrontNameResolver::AREA_CODE,
                        'store' => $feed->getStore()->getId(),
                    ])
                    ->setTemplateVars($vars)
                    ->addTo($email)
                    ->getTransport();
                $transport->sendMessage();
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}
