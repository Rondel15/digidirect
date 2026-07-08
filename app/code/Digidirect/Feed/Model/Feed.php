<?php

namespace Digidirect\Feed\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Digidirect\Feed\Api\Data\FeedInterface;
use Magento\Framework\Serialize\Serializer\Serialize;

/**
 * Feed Model
 * @method ResourceModel\Feed getResource()
 */
class Feed extends AbstractTemplate implements FeedInterface
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'digidirect_feed_feed';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'feed';

    /**
     * @var \Magento\Store\Model\Store
     */
    protected $store;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManagerInterface;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var UrlInterface
     */
    protected $urlManager;

    /**
     * Feed constructor.
     * @param Context $context
     * @param Registry $registry
     * @param Serialize $serializer
     * @param StoreManagerInterface $storeManagerInterface
     * @param Config $config
     * @param UrlInterface $urlManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Serialize $serializer,
        StoreManagerInterface $storeManagerInterface,
        Config $config,
        UrlInterface $urlManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->storeManagerInterface = $storeManagerInterface;
        $this->config = $config;
        $this->urlManager = $urlManager;

        parent::__construct($context, $registry, $serializer, $resource, $resourceCollection, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Digidirect\Feed\Model\ResourceModel\Feed');
    }

    /**
     * Model of store
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        if (!$this->store) {
            $this->store = $this->storeManagerInterface->getStore($this->getStoreId());
        }

        return $this->store;
    }

    /**
     * Rule IDs
     *
     * @return array
     */
    public function getRuleIds()
    {
        if (!$this->hasData('rule_ids')) {
            $this->setData('rule_ids', $this->getResource()->getRuleIds($this));
        }
        return $this->_getData('rule_ids');
    }

    /**
     * Email notification events
     *
     * @return array
     */
    public function getNotificationEvents()
    {
        if (!is_array($this->getData(self::NOTIFICATION_EVENTS))) {
            $this->setData(self::NOTIFICATION_EVENTS, explode(',', $this->getData(self::NOTIFICATION_EVENTS)));
        }

        return $this->getData(self::NOTIFICATION_EVENTS);
    }

    /**
     * Cron days
     *
     * @return array
     */
    public function getCronDay()
    {
        if (!is_array($this->getData(self::CRON_DAY))) {
            $this->setData(self::CRON_DAY, explode(',', $this->getData(self::CRON_DAY)));
        }

        return $this->getData(self::CRON_DAY);
    }

    /**
     * Cron time
     *
     * @return array
     */
    public function getCronTime()
    {
        if (!is_array($this->getData(self::CRON_TIME))) {
            $this->setData(self::CRON_TIME, explode(',', $this->getData(self::CRON_TIME)));
        }

        return $this->getData(self::CRON_TIME);
    }

    /**
     * Full url to feed
     *
     * @return string|false
     */
    public function getUrl()
    {
        $url = false;

        $filename = $this->getFilename();

        if ($this->getArchivation()) {
            $filename .= '.' . $this->getArchivation();
        }

        $path = $this->config->getBasePath() . DIRECTORY_SEPARATOR . $filename;

        if (file_exists($path)) {
            $url = $this->getStore()->getBaseUrl(DirectoryList::MEDIA) . 'feed/' . $filename;
        }

        return $url;
    }

    /**
     * Filename with extension
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->getData(self::FILENAME) . '.' . strtolower($this->getType());
    }

    /**
     * Preview filename with extension
     *
     * @return string
     */
    public function getPreviewFilename()
    {
        return $this->getData(self::FILENAME) . '.test' . '.' . strtolower($this->getType());
    }

    /**
     * Absolute feed path
     *
     * @return string
     */
    public function getFilePath()
    {
        return $this->config->getBasePath() . DIRECTORY_SEPARATOR . $this->getFilename();
    }

    /**
     * Preview feed absolute path
     *
     * @return string
     */
    public function getPreviewFilePath()
    {
        return $this->config->getBasePath() . DIRECTORY_SEPARATOR . $this->getPreviewFilename();
    }

    /**
     * Set template data (type, content settings) to feed
     *
     * @param Template $template
     *
     * @return $this
     */
    public function loadFromTemplate(Template $template)
    {
        $this->addData($template->getData());

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave()
    {
        $return = parent::beforeSave();

        if (!$this->getData('template_id')) {
            $this->setData('xml', ['schema' => '']);
            if (!$this->getData(self::FORMAT_SERIALIZED)) {
                $this->setData(self::FORMAT_SERIALIZED, $this->serializer->serialize($this->getData('xml')));
            }
        }

        return $return;
    }

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(self::FEED_ID);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setId($id)
    {
        return $this->setData(self::FEED_ID, $id);
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * Set name
     *
     * @param string $name
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Get Store ID
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * Set Store ID
     *
     * @param int $storeId
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * Set filename
     *
     * @param string $filename
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setFilename($filename)
    {
        return $this->setData(self::FILENAME);
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * Set type
     *
     * @param string $type
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * Get format serialized
     *
     * @return string
     */
    public function getFormatSerialized()
    {
        return $this->getData(self::FORMAT_SERIALIZED);
    }

    /**
     * Set format serialized
     *
     * @param string $formatSerialized
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setFormatSerialized($formatSerialized)
    {
        return $this->setData(self::FORMAT_SERIALIZED, $formatSerialized);
    }

    /**
     * Get is active
     *
     * @return bool
     */
    public function getIsActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * Set is active
     *
     * @param bool $isActive
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * Get generated at
     *
     * @return string
     */
    public function getGeneratedAt()
    {
        return $this->getData(self::GENERATED_AT);
    }

    /**
     * Set generated at
     *
     * @param string $generatedAt
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setGeneratedAt($generatedAt)
    {
        return $this->setData(self::GENERATED_AT, $generatedAt);
    }

    /**
     * Get generated cnt
     *
     * @return int
     */
    public function getGeneratedCnt()
    {
        return $this->getData(self::GENERATED_CNT);
    }

    /**
     * Set generated cnt
     *
     * @param int $generatedCnt
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setGeneratedCnt($generatedCnt)
    {
        return $this->setData(self::GENERATED_CNT, $generatedCnt);
    }

    /**
     * Get generated time
     *
     * @return int
     */
    public function getGeneratedTime()
    {
        return $this->getData(self::GENERATED_TIME);
    }

    /**
     * Set generated time
     *
     * @param int $generatedTime
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setGeneratedTime($generatedTime)
    {
        return $this->setData(self::GENERATED_TIME, $generatedTime);
    }

    /**
     * Get cron
     *
     * @return bool
     */
    public function getCron()
    {
        return $this->getData(self::CRON);
    }

    /**
     * Set cron
     *
     * @param bool $cron
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setCron($cron)
    {
        return $this->setData(self::CRON, $cron);
    }

    /**
     * Set cron
     *
     * @param string $cronDay
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setCronDay($cronDay)
    {
        return $this->setData(self::CRON_DAY, $cronDay);
    }

    /**
     * Set cron time
     *
     * @param string $cronTime
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setCronTime($cronTime)
    {
        return $this->setData(self::CRON_TIME, $cronTime);
    }

    /**
     * Get ftp
     *
     * @return bool
     */
    public function getFtp()
    {
        return $this->getData(self::FTP);
    }

    /**
     * Set ftp
     *
     * @param bool $ftp
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setFtp($ftp)
    {
        return $this->setData(self::FTP, $ftp);
    }

    /**
     * Get ftp protocol
     *
     * @return string
     */
    public function getFtpProtocol()
    {
        return $this->getData(self::FTP_PROTOCOL);
    }

    /**
     * Set ftp protocol
     *
     * @param string $ftpProtocol
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setFtpProtocol($ftpProtocol)
    {
        return $this->setData(self::FTP_PROTOCOL, $ftpProtocol);
    }

    /**
     * Get ftp host
     *
     * @return string
     */
    public function getFtpHost()
    {
        return $this->getData(self::FTP_HOST);
    }

    /**
     * Set ftp host
     *
     * @param string $ftpHost
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setFtpHost($ftpHost)
    {
        return $this->setData(self::FTP_HOST, $ftpHost);
    }

    /**
     * Get ftp user
     *
     * @return string
     */
    public function getFtpUser()
    {
        return $this->getData(self::FTP_USER);
    }

    /**
     * Set ftp user
     *
     * @param string $ftpUser
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setFtpUser($ftpUser)
    {
        return $this->setData(self::FTP_USER, $ftpUser);
    }

    /**
     * Get ftp password
     *
     * @return string
     */
    public function getFtpPassword()
    {
        return $this->getData(self::FTP_PASSWORD);
    }

    /**
     * Set ftp password
     *
     * @param string $ftpPassword
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setFtpPassword($ftpPassword)
    {
        return $this->setData(self::FTP_PASSWORD, $ftpPassword);
    }

    /**
     * Get ftp path
     *
     * @return string
     */
    public function getFtpPath()
    {
        return $this->getData(self::FTP_PATH);
    }

    /**
     * Set ftp path
     *
     * @param string $ftpPath
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setFtpPath($ftpPath)
    {
        return $this->setData(self::FTP_PATH, $ftpPath);
    }

    /**
     * Get ftp passive mode
     *
     * @return bool
     */
    public function getFtpPassiveMode()
    {
        return $this->getData(self::FTP_PASSIVE_MODE);
    }

    /**
     * Set ftp passive mode
     *
     * @param bool $ftpPassiveMode
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setFtpPassiveMode($ftpPassiveMode)
    {
        return $this->setData(self::FTP_PASSIVE_MODE, $ftpPassiveMode);
    }

    /**
     * Get uploaded at
     *
     * @return string|null
     */
    public function getUploadedAt()
    {
        return $this->getData(self::UPLOADED_AT);
    }

    /**
     * Set uploaded at
     *
     * @param string|null $uploadedAt
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setUploadedAt($uploadedAt)
    {
        return $this->setData(self::UPLOADED_AT, $uploadedAt);
    }

    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set created at
     *
     * @param string $createdAt
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get updated at
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * Set updated at
     *
     * @param string $updatedAt
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * Get ga source
     *
     * @return string|null
     */
    public function getGaSource()
    {
        return $this->getData(self::GA_SOURCE);
    }

    /**
     * Set ga source
     *
     * @param string|null $gaSource
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setGaSource($gaSource)
    {
        return $this->setData(self::GA_SOURCE, $gaSource);
    }

    /**
     * Get ga medium
     *
     * @return string|null
     */
    public function getGaMedium()
    {
        return $this->getData(self::GA_MEDIUM);
    }

    /**
     * Set ga medium
     *
     * @param string|null $gaMedium
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setGaMedium($gaMedium)
    {
        return $this->setData(self::GA_MEDIUM, $gaMedium);
    }

    /**
     * Get ga name
     *
     * @return string|null
     */
    public function getGaName()
    {
        return $this->getData(self::GA_NAME);
    }

    /**
     * Set ga name
     *
     * @param string|null $gaName
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setGaName($gaName)
    {
        return $this->setData(self::GA_NAME, $gaName);
    }

    /**
     * Get ga term
     *
     * @return string|null
     */
    public function getGaTerm()
    {
        return $this->getData(self::GA_TERM);
    }

    /**
     * Set ga term
     *
     * @param string|null $gaTerm
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setGaTerm($gaTerm)
    {
        return $this->setData(self::GA_TERM, $gaTerm);
    }

    /**
     * Get ga content
     *
     * @return string|null
     */
    public function getGaContent()
    {
        return $this->getData(self::GA_CONTENT);
    }

    /**
     * Set ga content
     *
     * @param string|null $gaContent
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setGaContent($gaContent)
    {
        return $this->setData(self::GA_CONTENT, $gaContent);
    }

    /**
     * Get notification emails
     *
     * @return string|null
     */
    public function getNotificationEmails()
    {
        return $this->getData(self::NOTIFICATION_EMAILS);
    }

    /**
     * Set notification emails
     *
     * @param string|null $notificationEmails
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setNotificationEmails($notificationEmails)
    {
        return $this->setData(self::NOTIFICATION_EMAILS, $notificationEmails);
    }

    /**
     * Set notification events
     *
     * @param string|null $notificationEvents
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setNotificationEvents($notificationEvents)
    {
        return $this->setData(self::NOTIFICATION_EVENTS, $notificationEvents);
    }

    /**
     * Get report enabled
     *
     * @return bool
     */
    public function getReportEnabled()
    {
        return $this->getData(self::REPORT_ENABLED);
    }

    /**
     * Set report enabled
     *
     * @param bool $reportEnabled
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setReportEnabled($reportEnabled)
    {
        return $this->setData(self::REPORT_ENABLED, $reportEnabled);
    }

    /**
     * Get archivation
     *
     * @return string|null
     */
    public function getArchivation()
    {
        return $this->getData(self::ARCHIVATION);
    }

    /**
     * Set archivation
     *
     * @param string|null $archivation
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setArchivation($archivation)
    {
        return $this->setData(self::ARCHIVATION, $archivation);
    }
}
