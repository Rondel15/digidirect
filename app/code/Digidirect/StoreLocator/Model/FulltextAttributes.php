<?php

namespace Digidirect\StoreLocator\Model;

use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Digidirect\AbstractEntity\Model\AbstractEntity;
use Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntityIndex;
use Digidirect\StoreLocator\Helper\Config as ConfigHelper;
use Digidirect\AbstractEntity\Helper\Data as Helper;
use Digidirect\AbstractEntity\Model\AttributeSetRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Eav\Model\AttributeManagement;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class FulltextAttributes
 * @package Digidirect\StoreLocator\Model
 */
class FulltextAttributes
{
    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var array
     */
    protected $tableNames = [];

    /**
     * @var array
     */
    protected $setAttributes = [];

    /**
     * @var AttributeManagement
     */
    protected $attributeManagement;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * FulltextAttributes constructor.
     * @param ConfigHelper $configHelper
     * @param Helper $helper
     * @param AttributeSetRepository $attributeSetRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AttributeManagement $attributeManagement
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ConfigHelper $configHelper,
        Helper $helper,
        AttributeSetRepository $attributeSetRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributeManagement $attributeManagement,
        StoreManagerInterface $storeManager = null
    ) {
        $this->attributeSetRepository = $attributeSetRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->configHelper = $configHelper;
        $this->helper = $helper;
        $this->attributeManagement = $attributeManagement;
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()->get(StoreManagerInterface::class);
    }

    /**
     * @param DataObject $object
     * @return $this
     */
    public function prepareFulltextAttributes($object)
    {
        $name = $object->getData(AbstractEntityIndex::TABLE_POSTFIX);
        if (in_array($name, $this->_getAllovedTables())) {
            $object->setData(AbstractEntityIndex::FULTEXT_COLUMNS, $this->_getTableFulltextAttributes($name));
        }
        return $this;
    }

    /**
     * @return array
     */
    protected function _getAllovedTables()
    {
        if (empty($this->tableNames)) {
            $this->_prepareFultextIndexData();
        }

        return $this->tableNames;
    }

    /**
     * @param string $name
     * @return array
     */
    protected function _getTableFulltextAttributes($name)
    {
        return $this->setAttributes[$name];
    }

    /**
     * @return $this
     */
    protected function _prepareFultextIndexData()
    {
        $availableSets = $this->configHelper->getEntities();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(AbstractEntityInterface::ATTRIBUTE_SET_ID, $availableSets, 'in')
            ->create();
        $defaultAttributes = $this->configHelper->getAttributes();
        foreach ($this->attributeSetRepository->getList($searchCriteria)->getItems() as $attributeSet) {
            foreach ($this->storeManager->getStores(true) as $store) {
                $name = $this->helper->getIndexTablePostfix($attributeSet->getAttributeSetName(), $store->getId());
                $this->tableNames[] = $name;
                $attributes = $this->attributeManagement->getAttributes(
                    AbstractEntity::ENTITY_TYPE,
                    $attributeSet->getAttributeSetId()
                );
                foreach ($attributes as $attribute) {
                    if (in_array($attribute->getAttributeCode(), $defaultAttributes)) {
                        $this->setAttributes[$name][] = $attribute->getAttributeCode();
                    }
                }
            }
            if (!empty($this->setAttributes[$name])) {
                $this->setAttributes[$name] = array_unique($this->setAttributes[$name]);
            }
        }
        $defaultTablePostfix = $this->helper->getIndexTablePostfix(ConfigHelper::ATTRIBUTE_SET_NAME);
        if (!in_array($defaultTablePostfix, $this->tableNames)) {
            $this->tableNames[] = $defaultTablePostfix;
            $this->setAttributes[$defaultTablePostfix] = $this->configHelper->getAttributes();
        }

        return $this;
    }
}
