<?php

namespace Digidirect\Feed\Api\Data;

interface FeedInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const FEED_ID = 'feed_id';
    const NAME = 'name';
    const STORE_ID = 'store_id';
    const FILENAME = 'filename';
    const TYPE = 'type';
    const FORMAT_SERIALIZED = 'format_serialized';
    const IS_ACTIVE = 'is_active';
    const GENERATED_AT = 'generated_at';
    const GENERATED_CNT = 'generated_cnt';
    const GENERATED_TIME = 'generated_time';
    const CRON = 'cron';
    const CRON_DAY = 'cron_day';
    const CRON_TIME = 'cron_time';
    const FTP = 'ftp';
    const FTP_PROTOCOL = 'ftp_protocol';
    const FTP_HOST = 'ftp_host';
    const FTP_USER = 'ftp_user';
    const FTP_PASSWORD = 'ftp_password';
    const FTP_PATH = 'ftp_path';
    const FTP_PASSIVE_MODE = 'ftp_passive_mode';
    const UPLOADED_AT = 'uploaded_at';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const GA_SOURCE = 'ga_source';
    const GA_MEDIUM = 'ga_medium';
    const GA_NAME = 'ga_name';
    const GA_TERM = 'ga_term';
    const GA_CONTENT = 'ga_content';
    const NOTIFICATION_EMAILS = 'notification_emails';
    const NOTIFICATION_EVENTS = 'notification_events';
    const REPORT_ENABLED = 'report_enabled';
    const ARCHIVATION = 'archivation';
    /**#@-*/

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @param int $id
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setId($id);

    /**
     * Get name
     *
     * @return string
     */
    public function getName();

    /**
     * Set name
     *
     * @param string $name
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setName($name);

    /**
     * Get Store ID
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Set Store ID
     *
     * @param int $storeId
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setStoreId($storeId);

    /**
     * Get filename
     *
     * @return string
     */
    public function getFilename();

    /**
     * Set filename
     *
     * @param string $filename
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setFilename($filename);

    /**
     * Get type
     *
     * @return string
     */
    public function getType();

    /**
     * Set type
     *
     * @param string $type
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setType($type);

    /**
     * Get format serialized
     *
     * @return string
     */
    public function getFormatSerialized();

    /**
     * Set format serialized
     *
     * @param string $formatSerialized
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setFormatSerialized($formatSerialized);

    /**
     * Get is active
     *
     * @return bool
     */
    public function getIsActive();

    /**
     * Set is active
     *
     * @param bool $isActive
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setIsActive($isActive);

    /**
     * Get generated at
     *
     * @return string
     */
    public function getGeneratedAt();

    /**
     * Set generated at
     *
     * @param string $generatedAt
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setGeneratedAt($generatedAt);

    /**
     * Get generated cnt
     *
     * @return int
     */
    public function getGeneratedCnt();

    /**
     * Set generated cnt
     *
     * @param int $generatedCnt
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setGeneratedCnt($generatedCnt);

    /**
     * Get generated time
     *
     * @return int
     */
    public function getGeneratedTime();

    /**
     * Set generated time
     *
     * @param int $generatedTime
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setGeneratedTime($generatedTime);

    /**
     * Get cron
     *
     * @return bool
     */
    public function getCron();

    /**
     * Set cron
     *
     * @param bool $cron
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setCron($cron);

    /**
     * Get cron
     *
     * @return string
     */
    public function getCronDay();

    /**
     * Set cron
     *
     * @param string $cronDay
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setCronDay($cronDay);

    /**
     * Get cron
     *
     * @return string
     */
    public function getCronTime();

    /**
     * Set cron time
     *
     * @param string $cronTime
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setCronTime($cronTime);

    /**
     * Get ftp
     *
     * @return bool
     */
    public function getFtp();

    /**
     * Set ftp
     *
     * @param bool $ftp
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setFtp($ftp);

    /**
     * Get ftp protocol
     *
     * @return string
     */
    public function getFtpProtocol();

    /**
     * Set ftp protocol
     *
     * @param string $ftpProtocol
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setFtpProtocol($ftpProtocol);

    /**
     * Get ftp host
     *
     * @return string
     */
    public function getFtpHost();

    /**
     * Set ftp host
     *
     * @param string $ftpHost
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setFtpHost($ftpHost);

    /**
     * Get ftp user
     *
     * @return string
     */
    public function getFtpUser();

    /**
     * Set ftp user
     *
     * @param string $ftpUser
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setFtpUser($ftpUser);

    /**
     * Get ftp password
     *
     * @return string
     */
    public function getFtpPassword();

    /**
     * Set ftp password
     *
     * @param string $ftpPassword
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setFtpPassword($ftpPassword);

    /**
     * Get ftp path
     *
     * @return string
     */
    public function getFtpPath();

    /**
     * Set ftp path
     *
     * @param string $ftpPath
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setFtpPath($ftpPath);

    /**
     * Get ftp passive mode
     *
     * @return bool
     */
    public function getFtpPassiveMode();

    /**
     * Set ftp passive mode
     *
     * @param bool $ftpPassiveMode
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setFtpPassiveMode($ftpPassiveMode);

    /**
     * Get uploaded at
     *
     * @return string|null
     */
    public function getUploadedAt();

    /**
     * Set uploaded at
     *
     * @param string|null $uploadedAt
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setUploadedAt($uploadedAt);

    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Set created at
     *
     * @param string $createdAt
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated at
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * Set updated at
     *
     * @param string $updatedAt
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Get ga source
     *
     * @return string|null
     */
    public function getGaSource();

    /**
     * Set ga source
     *
     * @param string|null $gaSource
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setGaSource($gaSource);

    /**
     * Get ga medium
     *
     * @return string|null
     */
    public function getGaMedium();

    /**
     * Set ga medium
     *
     * @param string|null $gaMedium
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setGaMedium($gaMedium);

    /**
     * Get ga name
     *
     * @return string|null
     */
    public function getGaName();

    /**
     * Set ga name
     *
     * @param string|null $gaName
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setGaName($gaName);

    /**
     * Get ga term
     *
     * @return string|null
     */
    public function getGaTerm();

    /**
     * Set ga term
     *
     * @param string|null $gaTerm
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setGaTerm($gaTerm);

    /**
     * Get ga content
     *
     * @return string|null
     */
    public function getGaContent();

    /**
     * Set ga content
     *
     * @param string|null $gaContent
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setGaContent($gaContent);

    /**
     * Get notification emails
     *
     * @return string|null
     */
    public function getNotificationEmails();

    /**
     * Set notification emails
     *
     * @param string|null $notificationEmails
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setNotificationEmails($notificationEmails);

    /**
     * Get notification events
     *
     * @return string|null
     */
    public function getNotificationEvents();

    /**
     * Set notification events
     *
     * @param string|null $notificationEvents
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setNotificationEvents($notificationEvents);

    /**
     * Get report enabled
     *
     * @return bool
     */
    public function getReportEnabled();

    /**
     * Set report enabled
     *
     * @param bool $reportEnabled
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setReportEnabled($reportEnabled);

    /**
     * Get archivation
     *
     * @return string|null
     */
    public function getArchivation();

    /**
     * Set archivation
     *
     * @param string|null $archivation
     * @return \Digidirect\Feed\Api\Data\FeedInterface
     */
    public function setArchivation($archivation);
}
