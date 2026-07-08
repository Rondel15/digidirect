<?php

namespace Digidirect\Feed\Model\Feed;

use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Filesystem\Io\Ftp;
use Magento\Framework\Filesystem\Io\Sftp;
use Digidirect\Feed\Model\Feed;

class Deliverer
{
    const STATUS_ERROR = 'error';
    const STATUS_SUCCESS = 'success';

    /**
     * Connection timeout in seconds
     */
    const CONNECTION_TIMEOUT = 10;

    /**
     * SFTP connection Model
     *
     * @var Sftp
     */
    protected $sftp;

    /**
     * FTP connection model
     *
     * @var Ftp
     */
    protected $ftp;

    /**
     * Event manager
     *
     * @var EventManager
     */
    protected $eventManager;

    /**
     * Email Notifier
     *
     * @var Notifier
     */
    protected $notifier;

    /**
     * Constructor
     *
     * @param Sftp $sftp
     * @param Ftp $ftp
     * @param EventManager $eventManager
     * @param Notifier $notifier
     */
    public function __construct(
        Sftp $sftp,
        Ftp $ftp,
        EventManager $eventManager,
        Notifier $notifier
    ) {
        $this->sftp = $sftp;
        $this->ftp = $ftp;
        $this->eventManager = $eventManager;
        $this->notifier = $notifier;
    }

    /**
     * Delivery feed
     *
     * @param Feed $feed
     * @return bool
     * @throws \Exception
     */
    public function delivery(Feed $feed)
    {
        try {
            $this->uploadFile($feed, $feed->getFilePath(), $feed->getFilename());
            $this->notifier->deliverySuccess($feed);
        } catch (\Exception $e) {
            $this->notifier->deliveryFail($feed, $e->getMessage());
            throw $e;
        }

        return true;
    }

    /**
     * Validate connection settings for feed
     *
     * @param Feed $feed
     * @return bool
     * @throws \Exception
     */
    public function validate(Feed $feed)
    {
        return $this->uploadFile($feed, 'test', 'deliverer.txt');
    }

    /**
     * Upload file to destination directory
     *
     * @param Feed $feed
     * @param string $source path to file or content
     * @param string $filename
     * @return bool
     * @throws \Exception
     */
    protected function uploadFile(Feed $feed, $source, $filename)
    {
        if ($feed->getFtpProtocol() == 'ftp') {
            $args = [
                'host' => $feed->getFtpHost(),
                'user' => $feed->getFtpUser(),
                'password' => $feed->getFtpPassword(),
                'passive' => (bool)$feed->getFtpPassiveMode(),
                'path' => $feed->getFtpPath(),
                'timeout' => self::CONNECTION_TIMEOUT,
            ];

            $this->ftp->open($args);
            $this->ftp->cd($feed->getFtpPath());
            $this->ftp->write($filename, $source);
            $this->ftp->close();
        } elseif ($feed->getFtpProtocol() == 'sftp') {
            if (!defined('NET_SFTP_LOCAL_FILE')) {
                define('NET_SFTP_LOCAL_FILE', 1);
            }
            if (!defined('NET_SFTP_STRING')) {
                define('NET_SFTP_STRING', 2);
            }

            $args = [
                'host' => $feed->getFtpHost(),
                'username' => $feed->getFtpUser(),
                'password' => $feed->getFtpPassword(),
                'timeout' => self::CONNECTION_TIMEOUT,
            ];

            $this->sftp->open($args);
            $this->sftp->cd($feed->getFtpPath());
            $this->sftp->write($filename, $source);
            $this->sftp->close();
        }

        return true;
    }
}
