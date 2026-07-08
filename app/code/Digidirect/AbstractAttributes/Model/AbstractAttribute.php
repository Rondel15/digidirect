<?php
namespace Digidirect\AbstractAttributes\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Option
 * @package Digidirect\AbstractAttributes\Model
 */
class AbstractAttribute extends AbstractModel implements \Digidirect\AbstractAttributes\Api\Data\AbstractAttributeInterface
{
    /**
     * @var \Digidirect\AbstractAttributes\Model\UrlProcessor\AttributeFactory
     */
    protected $urlProcessorFactory;

    /**
     * @var \Digidirect\AbstractAttributes\Model\UrlProcessor\Attribute
     */
    protected $urlProcessor;

    /**
     * @var \Digidirect\AbstractAttributes\Helper\Url
     */
    protected $urlHelper;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Digidirect\AbstractAttributes\Model\UrlProcessor\AttributeFactory $urlProcessorFactory
     * @param \Digidirect\AbstractAttributes\Helper\Url $urlHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Digidirect\AbstractAttributes\Model\UrlProcessor\AttributeFactory $urlProcessorFactory,
        \Digidirect\AbstractAttributes\Helper\Url $urlHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->urlProcessorFactory = $urlProcessorFactory;
        $this->urlHelper = $urlHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
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
    public function getAttributeId()
    {
        return $this->_getData(self::ATTRIBUTE_ID);
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
    public function getAttributeCode()
    {
        return $this->_getData(self::ATTRIBUTE_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributeCode($code)
    {
        $this->setData(self::ATTRIBUTE_CODE, $code);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeLabel()
    {
        $storeId = $this->getAttributeStoreId() ? $this->getAttributeStoreId() : $this->getStoreId();
        $storeKey = self::ATTRIBUTED_LABEL . '_' . $storeId;
        if ($this->hasData($storeKey)) {
            return $this->_getData($storeKey);
        }
        return $this->_getData(self::ATTRIBUTED_LABEL);
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributeLabel($label)
    {
        $this->setData(self::ATTRIBUTED_LABEL, $label);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->_getData(self::STATUS);
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
    public function getListingEnabled()
    {
        return $this->_getData(self::LISTING_ENABLED);
    }

    /**
     * {@inheritdoc}
     */
    public function setListingEnabled($listingEnabled)
    {
        $this->setData(self::LISTING_ENABLED, $listingEnabled);
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
     * Get option url
     * @param array $params
     * @return string
     */
    public function getUrl($params = [])
    {
        return $this->urlHelper->getAttributeUrl($this, $params);
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
    public function getPageTemplate()
    {
        return $this->_getData(self::PAGE_TEMPLATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setPageTemplate($template)
    {
        $this->setData(self::PAGE_TEMPLATE, $template);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomTemplate()
    {
        return $this->_getData(self::CUSTOM_TEMPLATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomTemplate($template)
    {
        $this->setData(self::CUSTOM_TEMPLATE, $template);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Digidirect\AbstractAttributes\Model\ResourceModel\AbstractAttribute');
    }

    /**
     * Process url rewrites
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
     * @return $this
     */
    public function deleteUrlRewrites()
    {
        $urlProcessor = $this->getUrlProcessor();
        $urlProcessor->deleteUrlRewrites($this);
        return $this;
    }

    /**
     * @return \Digidirect\AbstractAttributes\Model\UrlProcessor\Attribute
     */
    protected function getUrlProcessor()
    {
        if (null === $this->urlProcessor) {
            $this->urlProcessor = $this->urlProcessorFactory->create();
        }
        return $this->urlProcessor;
    }
}
