<?php

namespace Digidirect\AbstractAttributes\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Option
 *
 * @package Digidirect\AbstractAttributes\Model
 * @method \Digidirect\AbstractAttributes\Model\ResourceModel\Option getResource()
 * @method $this setRequestPath($requestPath)
 */
class Option extends AbstractModel implements \Digidirect\AbstractAttributes\Api\Data\OptionInterface
{
    /**
     * @var  \Magento\Eav\Model\Entity\Attribute\Option
     */
    protected $_defaultOption;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\OptionFactory
     */
    protected $_optionFactory;

    /**
     * @var OptionRepository
     */
    protected $optionRepository;

    /**
     * @var \Digidirect\AbstractAttributes\Model\UrlProcessor\OptionFactory
     */
    protected $urlProcessorFactory;

    /**
     * @var \Digidirect\AbstractAttributes\Model\UrlProcessor\Option
     */
    protected $urlProcessor;

    /**
     * @var \Digidirect\AbstractAttributes\Helper\Url
     */
    protected $urlHelper;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeOptionManagementInterfaceFactory
     */
    protected $optionManagementFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * Option constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param OptionRepository $optionRepository
     * @param \Digidirect\AbstractAttributes\Model\UrlProcessor\OptionFactory $urlProcessorFactory
     * @param \Digidirect\AbstractAttributes\Helper\Url $urlHelper
     * @param \Magento\Catalog\Api\ProductAttributeOptionManagementInterfaceFactory $optionManagementFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        OptionRepository $optionRepository,
        \Digidirect\AbstractAttributes\Model\UrlProcessor\OptionFactory $urlProcessorFactory,
        \Digidirect\AbstractAttributes\Helper\Url $urlHelper,
        \Magento\Catalog\Api\ProductAttributeOptionManagementInterfaceFactory $optionManagementFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->optionRepository = $optionRepository;
        $this->urlProcessorFactory = $urlProcessorFactory;
        $this->urlHelper = $urlHelper;
        $this->optionManagementFactory = $optionManagementFactory;
        $this->storeManager = $storeManager;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->_init('Digidirect\AbstractAttributes\Model\ResourceModel\Option');
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionId()
    {
        return $this->_getData(self::OPTION_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setOptionId($optionId)
    {
        $this->setData(self::OPTION_ID, $optionId);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreId()
    {
        return $this->_getData(self::STORE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreId($storeId)
    {
        $this->setData(self::STORE_ID, $storeId);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrlKey()
    {
        return $this->_getData(self::URL_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function setUrlKey($urlKey)
    {
        $this->setData(self::URL_KEY, $urlKey);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        if (!$this->hasData(self::LABEL)) {
            $storeId = $this->storeManager->getStore()->getId();
            $label = $this->_getData('label_' . $storeId) ?: $this->getDefaultLabel();
            $this->setLabel($label);
        }
        return $this->_getData(self::LABEL);
    }

    /**
     * {@inheritdoc}
     */
    public function setLabel($label)
    {
        $this->setData(self::LABEL, $label);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultLabel()
    {
        return $this->_getData(self::DEFAULT_LABEL);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultLabel($label)
    {
        $this->setData(self::DEFAULT_LABEL, $label);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getImage()
    {
        return $this->_getData(self::IMAGE);
    }

    /**
     * {@inheritdoc}
     */
    public function setImage($image)
    {
        $this->setData(self::IMAGE, $image);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->_getData(self::DESCRIPTION);
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($description)
    {
        $this->setData(self::DESCRIPTION, $description);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getListing()
    {
        return (int)$this->_getData(self::LISTING);
    }

    /**
     * {@inheritdoc}
     */
    public function setListing($listing)
    {
        $this->setData(self::LISTING, $listing);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isListingEnabled()
    {
        return (bool)$this->_getData(self::LISTING);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute()
    {
        if (!$this->hasData(self::ATTRIBUTE) && ($id = $this->getOptionId())) {
            $this->setAttribute($this->optionRepository->getAbstractAttribute($id, $this->getStoreId()));
        }
        return $this->_getData(self::ATTRIBUTE);
    }

    /**
     * {@inheritdoc}
     */
    public function setAttribute(\Digidirect\AbstractAttributes\Api\Data\AbstractAttributeInterface $aa)
    {
        $this->setData(self::ATTRIBUTE, $aa);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeId()
    {
        return (int)$this->_getData(self::ATTRIBUTE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributeId($id)
    {
        $this->setData(self::ATTRIBUTE_ID, $id);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return (int)$this->_getData(self::STATUS);
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($status)
    {
        $this->setData(self::STATUS, $status);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIncludeInWidget()
    {
        return (int)$this->_getData(self::INCLUDE_IN_WIDGET);
    }

    /**
     * {@inheritdoc}
     */
    public function setIncludeInWidget($includeInWidget)
    {
        $this->setData(self::INCLUDE_IN_WIDGET, $includeInWidget);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidgetLogo()
    {
        return (int)$this->_getData(self::WIDGET_LOGO);
    }

    /**
     * {@inheritdoc}
     */
    public function setWidgetLogo($widgetLogo)
    {
        $this->setData(self::WIDGET_LOGO, $widgetLogo);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetaTitle()
    {
        return $this->_getData(self::META_TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setMetaTitle($metaTitle)
    {
        $this->setData(self::META_TITLE, $metaTitle);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetaDesc()
    {
        return $this->_getData(self::META_DESC);
    }

    /**
     * {@inheritdoc}
     */
    public function setMetaDesc($metaDesc)
    {
        $this->setData(self::META_DESC, $metaDesc);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition()
    {
        return (int)$this->_getData(self::POSITION);
    }

    /**
     * {@inheritdoc}
     */
    public function setPosition($position)
    {
        $this->setData(self::POSITION, $position);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        return (int)$this->_getData(self::SORT_ORDER);
    }

    /**
     * {@inheritdoc}
     */
    public function setSortOrder($sortOrder)
    {
        $this->setData(self::SORT_ORDER, $sortOrder);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCmsBlock()
    {
        return (int)$this->_getData(self::CMS_BLOCK);
    }

    /**
     * {@inheritdoc}
     */
    public function setCmsBlock($blockId)
    {
        $this->setData(self::CMS_BLOCK, $blockId);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLayoutUpdate()
    {
        return $this->_getData(self::LAYOUT_UPDATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setLayoutUpdate($layout)
    {
        $this->setData(self::LAYOUT_UPDATE, $layout);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLayoutUpdateXml()
    {
        return $this->_getData(self::LAYOUT_UPDATE_XML);
    }

    /**
     * {@inheritdoc}
     */
    public function setLayoutUpdateXml($layoutXml)
    {
        $this->setData(self::LAYOUT_UPDATE_XML, $layoutXml);
        return $this;
    }

    /**
     * Get request path
     *
     * @return string
     */
    public function getRequestPath()
    {
        return $this->_getData('request_path');
    }

    /**
     * Process url rewrites
     *
     * @return $this
     */
    public function processUrlRewrites()
    {
        $urlProcessor = $this->getUrlProcessor();
        $urlProcessor->processUrlRewrites($this);
        return $this;
    }

    /**
     * Delete url rewrites
     *
     * @return $this
     */
    public function deleteUrlRewrites()
    {
        $urlProcessor = $this->getUrlProcessor();
        $urlProcessor->deleteUrlRewrites($this);
        return $this;
    }

    /**
     * Get option url
     *
     * @param array $params
     * @return string
     */
    public function getUrl($params = [])
    {
        return $this->urlHelper->getOptionUrl($this, $params);
    }

    /**
     * Delete attribute option
     *
     * @param string|null $code
     * @param int|null $id
     * @return $this
     */
    public function deleteAttributeOption($code = null, $id = null)
    {
        if (null === $id) {
            $id = $this->getOptionId();
        }

        if (!$code) {
            $code = $this->getResource()->getAttributeCode(
                $this->getResource()->getAttributeId($id)
            );
        }

        /** @var \Magento\Catalog\Api\ProductAttributeOptionManagementInterface $optionManagement */
        $optionManagement = $this->optionManagementFactory->create();
        $optionManagement->delete($code, $id);

        return $this;
    }

    /**
     * @return \Digidirect\AbstractAttributes\Model\UrlProcessor\Option
     */
    protected function getUrlProcessor()
    {
        if (null === $this->urlProcessor) {
            $this->urlProcessor = $this->urlProcessorFactory->create();
        }
        return $this->urlProcessor;
    }

    /**
     * @param string $createdAt
     * @return $this
     */
    public function setAaOptionCreatedAt($createdAt)
    {
        $this->setData(self::CREATED_AT, $createdAt);
        return $this;
    }

    /**
     * @param string $updatedAt
     * @return $this
     */
    public function setAaOptionUpdatedAt($updatedAt)
    {
        $this->setData(self::UPDATED_AT, $updatedAt);
        return $this;
    }

    /**
     * @return string
     */
    public function getAaOptionCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @return string
     */
    public function getAaOptionUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }
}
