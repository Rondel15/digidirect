<?php
namespace Digidirect\CollectAbstractEntity\Model;

use Digidirect\CollectAbstractEntity\Helper\Config;
use Digidirect\CollectAbstractEntity\Api\Data\CollectFields\Constants;
use Digidirect\CollectAbstractEntity\Api\Data\CollectPlaceInterface;
use Digidirect\AbstractEntity\Api\AbstractEntityRepositoryInterface\Proxy as AbstractEntityRepositoryInterface;
use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Digidirect\AbstractEntity\Model\AbstractEntity;
use Digidirect\AbstractEntity\Model\AbstractEntity\UrlProcessorFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Api\FilterFactory;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class AbstractEntityCollectPlaceAdapter
 * @package Digidirect\CollectAbstractEntity\Model
 */
class AbstractEntityCollectPlace extends AbstractEntity implements CollectPlaceInterface, AbstractEntityInterface
{
    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * @var array
     */
    protected $configMatrix = [];

    /**
     * AbstractEntityCollectPlace constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param StoreManagerInterface $storeManager
     * @param UrlProcessorFactory $urlProcessorFactory
     * @param FilterManager $filterManager
     * @param AbstractEntityRepositoryInterface $abstractEntityRepository
     * @param Config $configHelper
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @param SearchCriteriaBuilder|null $searchCriteriaBuilder
     * @param FilterFactory|null $filterFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        StoreManagerInterface $storeManager,
        UrlProcessorFactory $urlProcessorFactory,
        FilterManager $filterManager,
        AbstractEntityRepositoryInterface $abstractEntityRepository,
        Config $configHelper,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [],
        SearchCriteriaBuilder $searchCriteriaBuilder = null,
        FilterFactory $filterFactory = null
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $storeManager,
            $urlProcessorFactory,
            $filterManager,
            $abstractEntityRepository,
            $resource,
            $resourceCollection,
            $data,
            $searchCriteriaBuilder,
            $filterFactory
        );
        $this->configHelper = $configHelper;
        $this->configMatrix = $configHelper->getCollectFieldsMatrix();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getEntityFieldValueForCollect(Constants::COLLECT_FIELD_NAME);
    }

    /**
     * Get Collect Place Address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->getEntityFieldValueForCollect(Constants::COLLECT_FIELD_ADDRESS);
    }

    /**
     * Get Collect Place Longitude
     *
     * @return string
     */
    public function getLongitude()
    {
        return $this->getEntityFieldValueForCollect(Constants::COLLECT_FIELD_LONGITUDE);
    }

    /**
     * Get Collect Place Latitude
     *
     * @return string|null
     */
    public function getLatitude()
    {
        return $this->getEntityFieldValueForCollect(Constants::COLLECT_FIELD_LATITUDE);
    }

    /**
     * @return string
     */
    public function getPostcode()
    {
        return $this->getEntityFieldValueForCollect(Constants::COLLECT_FIELD_POSTCODE);
    }

    /**
     * @param string $collectField
     * @return string|null
     */
    public function getEntityFieldValueForCollect($collectField)
    {
        $field = $this->getEntityFieldCodeForCollect($collectField);
        return $field ? $this->getData($field) : null;
    }

    /**
     * @param string $collectField
     * @return string|bool
     */
    public function getEntityFieldCodeForCollect($collectField)
    {
        return array_key_exists($collectField, $this->configMatrix) ?
            $this->configMatrix[$collectField] : false;
    }
}
