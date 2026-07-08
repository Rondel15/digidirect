<?php

namespace Digidirect\Blog\Sql;

use Digidirect\Blog\Api\Data\CategoryContentInterface;
use Digidirect\Blog\Model\CurrentStoreFetcher;
use Digidirect\Blog\Model\ResourceModel\Category;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\Store;

class CategoryInformationSave extends AbstractDb implements InformationSaveInterface,
 CurrentStoreContentCheckerInterface, IfNullProcessorInterface
{
    /**
     * @var CurrentStoreFetcher
     */
    protected $currentStore;

    /**
     * @var array
     */
    protected $cacheByEntity = [];

    /**
     * @var null|array
     */
    protected $tableDescription = null;

    /**
     * CategoryInformationSave constructor.
     *
     * @param Context $context
     * @param CurrentStoreFetcher $currentStoreFetcher
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        CurrentStoreFetcher $currentStoreFetcher,
        $connectionName = null
    ) {
        $this->currentStore = $currentStoreFetcher;
        parent::__construct($context, $connectionName);
    }

    /**
     * @param \Digidirect\Blog\Model\Category $entity
     * @return null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveInformation($entity)
    {
        $data = [
            CategoryContentInterface::STORE_ID => $this->currentStore->getCurrentStoreId(),
            CategoryContentInterface::CATEGORY_ID => $entity->getId(),
        ];

        foreach ($entity->getData() as $key => $value) {
            if (!is_scalar($value) || isset($data[$key])) {
                continue;
            }
            $data[$key] = $value;
        }

        $columns = $this->getConnection()->describeTable($this->getMainTable());

        foreach ($data as $key => $value) {
            if (!array_key_exists($key, $columns)) {
                unset($data[$key]);
            }
        }
        $this->saveInformationSql($data);

        if($entity->getIsNewCategory()) {
            $storeToSave = $data[CategoryContentInterface::STORE_ID] ?? Store::DEFAULT_STORE_ID;
            /**
             * If category is new and it is being saved not for default store view
             * we need to save it in default store view with disabled status
             */
            if($storeToSave != Store::DEFAULT_STORE_ID) {
                $data[CategoryContentInterface::STORE_ID] = Store::DEFAULT_STORE_ID;
                $data['status'] = 0;
                $this->saveInformationSql($data);
            }
        }
        return;
    }

    /**
     * @param [] $data
     * @return void
     */
    private function saveInformationSql($data)
    {
        $this->getConnection()->insertOnDuplicate(
            $this->getMainTable(),
            [
                $data,
            ]
        );
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_setMainTable(Category::DIGIDIRECT_BLOG_CATEGORY_INFORMATION_TABLE);
    }

    /**
     * @param int $entityId
     * @param int $storeId
     * @return bool
     */
    public function hasStoreViewContent(int $entityId, int $storeId): bool
    {
        $cacheKey = $entityId . $storeId . $this->getMainTable();
        if (!isset($this->cacheByEntity[$cacheKey])) {

            $select = $this->getConnection()->select()
                ->from($this->getMainTable())
                ->where(CategoryContentInterface::STORE_ID . '= ?', $storeId)
                ->where(CategoryContentInterface::CATEGORY_ID . '= ?', $entityId);
            $has = $this->getConnection()->fetchRow($select);

            $this->cacheByEntity[$cacheKey] = !empty($has);
        }

        return $this->cacheByEntity[$cacheKey] ?? false;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getMainTableColumns()
    {
        if (null === $this->tableDescription) {
            $this->tableDescription = $this->getConnection()->describeTable($this->getMainTable());
        }
        return array_keys($this->tableDescription);
    }

    /**
     * @param mixed $column
     * @param mixed $value
     * @return \Zend_Db_Expr
     */
    public function processIfNull($column, $value)
    {
        $generalExpr = $this->getStoreViewSpecificTable() . '.' . $column . ' =' . $value;
        return $this->getConnection()->getIfNullSql(
            $generalExpr,
            CategoryInformationJoin::DEFAULT_STORE_COLUMN_PREFIX . $generalExpr
        );
    }

    /**
     * @return string
     */
    public function getStoreViewSpecificTable(): string
    {
        return Category::DIGIDIRECT_BLOG_CATEGORY_INFORMATION_TABLE;
    }

    /**
     * @return string
     */
    public function getDefaultContentPrefix(): string
    {
        return CategoryInformationJoin::DEFAULT_STORE_COLUMN_PREFIX;
    }
}
