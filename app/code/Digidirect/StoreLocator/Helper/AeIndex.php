<?php

namespace Digidirect\StoreLocator\Helper;

use Digidirect\AbstractEntity\Helper\Data as AeHelper;
use Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntityIndex;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Magento\Framework\App\Cache;
use Magento\Framework\App\Helper\Context;

class AeIndex extends \Magento\Framework\App\Helper\AbstractHelper
{
    const DIGIDIRECT_STORE_LOCATOR_CACHE_TABLES = 'digidirect_storelocator_cache_tables';

    /**
     * @var array
     */
    protected $existingTables = [];

    /**
     * @var Cache
     */
    protected $cacheManager;

    /**
     * @var AbstractEntityIndex
     */
    protected $abstractEntityIndex;

    /**
     * @var CollectionFactory
     */
    protected $attributeSetCollectionFactory;

    /**
     * @var AeHelper
     */
    protected $aeHelper;

    /**
     * @var array
     */
    protected $describedAttributes = [];

    /**
     * AeIndex constructor.
     * @param AbstractEntityIndex $abstractEntityIndex
     * @param CollectionFactory $collectionFactory
     * @param Cache $cache
     * @param AeHelper $aeHelper
     * @param Context $context
     */
    public function __construct(
        AbstractEntityIndex $abstractEntityIndex,
        CollectionFactory $collectionFactory,
        Cache $cache,
        AeHelper $aeHelper,
        Context $context
    ) {
        parent::__construct($context);
        $this->cacheManager = $cache;
        $this->abstractEntityIndex = $abstractEntityIndex;
        $this->attributeSetCollectionFactory = $collectionFactory;
        $this->aeHelper = $aeHelper;
    }

    /**
     * @param string $attribteSetName
     * @param string $attribute
     * @return bool
     */
    public function isAttributeDescribed($attribteSetName, $attribute)
    {
        if (!isset($this->describedAttributes[$attribteSetName])) {
            $this->describedAttributes[$attribteSetName] = [];
            $tableName = $this->getExistsTableNameByAttributeSetName($attribteSetName);
            if ($tableName !== null) {
                $this->describedAttributes[$attribteSetName] = array_keys(
                    $this->abstractEntityIndex
                        ->getConnection()
                        ->describeTable($tableName)
                );
            }
        }

        return in_array($attribute, $this->describedAttributes[$attribteSetName]);
    }

    /**
     * Get AE index table name by attribute set name and check if table exists
     *
     * @param string $attributeSetName
     * @return string|null
     */
    public function getExistsTableNameByAttributeSetName($attributeSetName)
    {
        $suggestedTableName = AbstractEntityIndex::TABLE_NAME
            . $this->aeHelper->getIndexTablePostfix($attributeSetName);
        return $this->tableExists($suggestedTableName) ? $suggestedTableName : null;
    }

    /**
     * @param string $tableName
     * @return bool
     */
    protected function tableExists($tableName)
    {
        if (!isset($this->existingTables[$tableName])) {
            $cacheKey = static::DIGIDIRECT_STORE_LOCATOR_CACHE_TABLES . '_' . $tableName;
            $tableInCache = $this->cacheManager->load($cacheKey);
            try {
                $tables = $tableInCache;
            } catch (\Throwable $exception) {
                $tables = [];
            }
            if (!$tables) {
                $tableExists = !empty($this->abstractEntityIndex->getConnection()->getTables($tableName));
                if (false !== $tableExists) {
                    $this->cacheManager->save(
                        $tableExists,
                        $cacheKey,
                        [\Magento\Framework\View\Element\AbstractBlock::CACHE_GROUP]
                    );
                }
                $this->existingTables[$tableName] = $tableExists;
            } else {
                $this->existingTables[$tableName] = $tables;
            }
        }
        return !empty($this->existingTables[$tableName]);
    }

    /**
     * @param int $attributeSetId
     * @return Set
     */
    public function getAttributeSetById($attributeSetId)
    {
        return $this->attributeSetCollectionFactory->create()
            ->addFieldToFilter(
                'attribute_set_id',
                $attributeSetId
            )
            ->getFirstItem();
    }
}
