<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Popup;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Plumrocket\Newsletterpopup\Api\Data\PopupThemeInterface;
use Plumrocket\Newsletterpopup\Model\Popup\Thumbnail\Theme\Generator as ThumbnailGenerator;
use Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\CollectionFactory as PopupCollectionFactory;
use Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\Theme as ThemeResource;

/**
 * @since 4.0.0
 */
class Theme extends AbstractModel implements PopupThemeInterface
{
    /**
     * @var \Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\CollectionFactory
     */
    private $popupCollectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * @var ThumbnailGenerator
     */
    private $thumbnailGenerator;

    /**
     * @param \Magento\Framework\Model\Context                                        $context
     * @param \Magento\Framework\Registry                                             $registry
     * @param \Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\CollectionFactory $popupCollectionFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime                             $dateTime
     * @param \Plumrocket\Newsletterpopup\Model\Popup\Thumbnail\Theme\Generator       $thumbnailGenerator
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null            $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null                      $resourceCollection
     * @param array                                                                   $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        PopupCollectionFactory $popupCollectionFactory,
        DateTime $dateTime,
        ThumbnailGenerator $thumbnailGenerator,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->popupCollectionFactory = $popupCollectionFactory;
        $this->dateTime = $dateTime;
        $this->thumbnailGenerator = $thumbnailGenerator;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init(ThemeResource::class);
    }

    /**
     * @inheritDoc
     */
    public function isBuildIn(): bool
    {
        return (int) $this->getOrigData('base_template_id') === -1;
    }

    /**
     * @inheritDoc
     */
    public function getIdentifier(): string
    {
        return (string) $this->_getData(self::IDENTIFIER);
    }

    /**
     * @inheritDoc
     */
    public function setIdentifier(string $identifier): PopupThemeInterface
    {
        return $this->setData(self::IDENTIFIER, $identifier);
    }

    /**
     * @inheritDoc
     */
    public function getHtml(): string
    {
        return (string) $this->_getData(self::HTML);
    }

    /**
     * @inheritDoc
     */
    public function setHtml(string $html): TemplateInterface
    {
        return $this->setData(self::HTML, $html);
    }

    /**
     * @inheritDoc
     */
    public function getCss(): string
    {
        return (string) $this->_getData(self::CSS);
    }

    /**
     * @inheritDoc
     */
    public function setCss(string $css): TemplateInterface
    {
        return $this->setData(self::CSS, $css);
    }

    /**
     * @inheritDoc
     */
    public function getDefaultConfiguration(): string
    {
        return (string) $this->_getData(self::DEFAULT_CONFIGURATION);
    }

    /**
     * @inheritDoc
     */
    public function setDefaultConfiguration(string $defaultConfiguration): PopupThemeInterface
    {
        return $this->setData(self::DEFAULT_CONFIGURATION, $defaultConfiguration);
    }

    public function beforeSave()
    {
        if (!$this->getCanSaveBaseTemplates()) {
            if ($this->isBuildIn()) {
                // It's base template, create new.
                $this->setBaseTemplateId($this->getOrigData('entity_id'));
                $this->setId(null);
            } elseif ($this->isObjectNew()) {
                $this->setBaseTemplateId($this->getOrigData('base_template_id'));
            }
        }

        // Set dates.
        $date = $this->dateTime->gmtDate();
        if ($this->isObjectNew()) {
            $this->setData('created_at', $date);
            $this->setData('updated_at', null);
        } else {
            $this->setData('updated_at', $date);
        }

        return parent::beforeSave();
    }

    public function delete()
    {
        if (! $this->canDelete()) {
            return $this;
        }

        return parent::delete();
    }

    public function canDelete(): bool
    {
        if ($this->isBase()) {
            return false;
        }

        /** @var \Plumrocket\Newsletterpopup\Model\ResourceModel\Popup\Collection $popupCollection */
        $popupCollection = $this->popupCollectionFactory->create();

        $hasPopups = $popupCollection
            ->addFieldToFilter('template_id', $this->getId())
            ->getSize();

        return ! $hasPopups;
    }

    protected function _afterLoad()
    {
        $this->setIsTemplate(true);
        return parent::_afterLoad();
    }

    public function setIsObjectNew($flag = true)
    {
        $this->setCanSaveBaseTemplates($flag);
        $this->getResource()->useIsObjectNew($flag);
        $this->isObjectNew($flag);
        return $this;
    }

    /**
     * The difference between "isBase" and "isBuildIn" is that "isBase" can load model
     * @deprecated since 4.0.0 - model should not load themself
     *
     * @return bool
     */
    public function isBase()
    {
        if (! $this->hasData('base_template_id')) {
            $this->load($this->getEntityId());
        }

        return $this->isBuildIn();
    }

    /**
     * @deprecated since 4.0.0
     * @see \Plumrocket\Newsletterpopup\Model\Popup\Thumbnail\Theme\Generator::generate
     *
     * @return bool
     */
    public function generateThumbnail()
    {
        return $this->thumbnailGenerator->generate((int) $this->getId());
    }

    /**
     * @deprecated since 4.0.0
     * @see \Plumrocket\Newsletterpopup\Model\Popup\Thumbnail\Theme\Generator::getImagePath
     *
     * @param false $forWeb
     * @return string
     */
    public function getThumbnailFilePath($forWeb = false)
    {
        return $this->thumbnailGenerator->getImagePath((int) $this->getId(), $forWeb);
    }

    /**
     * @param false $forWeb
     * @return string
     * @deprecated since 4.0.0
     * @see \Plumrocket\Newsletterpopup\Model\Popup\Thumbnail\Theme\Generator::getImageCachePath
     */
    public function getThumbnailCacheFilePath($forWeb = false)
    {
        return $this->thumbnailGenerator->getImageCachePath((int) $this->getId(), $forWeb);
    }
}
