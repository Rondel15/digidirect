<?php

namespace Digidirect\Blog\Model;

use Digidirect\Blog\Api\Data\ImageInterface;
use Digidirect\Blog\Api\Data\PostInterface;
use Digidirect\Blog\Helper\Data;
use Digidirect\Blog\Model\Config\Provider\Status;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class Post
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Post extends \Magento\Framework\Model\AbstractModel implements PostInterface, ImageInterface, IdentityInterface
{
    const CACHE_PREFIX = 'blog_post_cache';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'digidirect_blog_post';

    /**
     * @var ImageProcessor
     */
    protected $imageProcessor;

    /**
     * @var []
     */
    protected $imagesFields = [
        'image',
        'image_thumb',
        'image_trending',
    ];

    /**
     * @var UrlModel
     */
    protected $urlModel;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var IdentitiesGenerator
     */
    protected $identitiesGenerator;

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * Post constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ResourceModel\Post $resource
     * @param ResourceModel\Post\Collection $resourceCollection
     * @param ImageProcessor $imageProcessor
     * @param UrlModel $urlModel
     * @param IdentitiesGenerator $identitiesGenerator
     * @param Data $dataHelper
     * @param TimezoneInterface $timezoneInterface
     * @param array $imagesFields
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Digidirect\Blog\Model\ResourceModel\Post $resource,
        \Digidirect\Blog\Model\ResourceModel\Post\Collection $resourceCollection,
        \Digidirect\Blog\Model\ImageProcessor $imageProcessor,
        UrlModel $urlModel,
        IdentitiesGenerator $identitiesGenerator,
        Data $dataHelper,
        TimezoneInterface $timezoneInterface,
        array $imagesFields = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection
        );
        $this->identitiesGenerator = $identitiesGenerator;
        $this->imageProcessor = $imageProcessor;
        $this->urlModel = $urlModel;
        $this->dataHelper = $dataHelper;
        $this->localeDate = $timezoneInterface;
        if (!empty($imagesFields)) {
            $this->imagesFields = $imagesFields;
        }
    }

    /**
     * Processing object before save data
     *
     * @return $this
     */
    public function beforeSave()
    {
        parent::beforeSave();
        $this->imageProcessor->prepareImageToSave($this);
        return $this;
    }

    /**
     * Processing object after save data
     *
     * @return \Magento\Framework\Model\AbstractModel
     */
    public function afterSave()
    {
        $this->imageProcessor->saveImages($this);
        return parent::afterSave();
    }

    /**
     * @return array
     */
    public function getImagesFields()
    {
        return $this->imagesFields;
    }

    /**
     * @param int $width
     * @param int $height
     * @return bool|string
     * @throws \Exception
     */
    public function getThumbnail($width = null, $height = null)
    {
        return $this->imageProcessor->resizeImage($this, 'image_thumb', $width, $height);
    }

    /**
     * @param int $width
     * @param int $height
     * @return bool|string
     * @throws \Exception
     */
    public function getMainImage($width = null, $height = null)
    {
        return $this->imageProcessor->resizeImage($this, 'image', $width, $height);
    }

    /**
     * @param string $type
     * @param int $width
     * @param int $height
     * @return bool|string
     * @throws LocalizedException
     */
    public function getImageByType($type, $width = null, $height = null)
    {
        if (!in_array($type, $this->imagesFields)) {
            throw new LocalizedException(__('No such type'));
        }

        return $this->imageProcessor->resizeImage($this, $type, $width, $height);
    }

    /**
     * @return string
     */
    public function getViewUrl()
    {
        return rtrim($this->urlModel->getViewPostUrl($this), '/');
    }

    /**
     * Get Post URL path without domain
     *
     * @return string
     * @throws LocalizedException
     */
    public function getViewUrlPath()
    {
        return $this->urlModel->getViewPostUrlPath($this);
    }

    /**
     * @return bool
     * @throws LocalizedException
     */
    public function isActive()
    {
        return $this->getStatus() == Status::STATUS_ENABLED;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getCreatedDate()
    {
        $publishDate = $this->getPublishDate();
        $dateFormat = $this->dataHelper->getGeneralSettingsConfig('date_format');
        $localize = $this->dataHelper->isDateLocalizationRequired();
        if ($localize) {
            $date = $this->localeDate->date(
                $publishDate
            );
            $formatted = $date->format($dateFormat);

            return $formatted;
        }
        $date = new \DateTime($publishDate);

        return $date->format($dateFormat);
    }

    /**
     * @param int $width
     * @param int $height
     * @return bool|string
     * @throws \Exception
     */
    public function getImageUrl($width = null, $height = null)
    {
        $imageUrl = $this->getThumbnail($width, $height);
        if (!$imageUrl) {
            $imageUrl = $this->getMainImage($width, $height);
        }
        return $imageUrl;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getIdentities()
    {
        $identities = $this->identitiesGenerator->getIdentities($this, self::CACHE_PREFIX);
        $categories = $this->getCategoryId();
        if (!is_array($categories)) {
            $categories = [];
        }

        foreach ($categories as $id) {
            $identities[] = $this->identitiesGenerator->makeIdentity($id, Category::CACHE_TAG_PREFIX);
        }
        return $identities;
    }

    /**
     * @return mixed
     */
    public function getAvailableStoresIds()
    {
        $storeIds = $this->_resource->getStoreRelationCategoryByPostId($this->getId());
        return array_reduce($storeIds, function ($acc, $element) {
            if (!empty($element)) {
                $acc[] = array_shift($element);
            }
            return $acc;
        }, []);
    }
}
