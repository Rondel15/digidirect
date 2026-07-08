<?php

namespace Digidirect\Blog\Model\ResourceModel\Post;

use Digidirect\Blog\Api\Data\CategoryContentInterface;
use Digidirect\Blog\Api\Data\PostContentInterface;
use Digidirect\Blog\Api\Data\PostInterface;
use Digidirect\Blog\Model\Config\Provider\Status;
use Digidirect\Blog\Model\CurrentStoreFetcher;
use Digidirect\Blog\Model\ResourceModel\Category;
use Digidirect\Blog\Model\ResourceModel\Post;
use Digidirect\Blog\Model\ResourceModel\Tag;
use Digidirect\Blog\Model\StoreContent\DataModifier;
use Digidirect\Blog\Sql\CategoryInformationJoin;
use Digidirect\Blog\Sql\DdlRegistry;
use Digidirect\Blog\Sql\PostInformationJoin;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\Store;

/**
 * Class Collection
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * @var CategoryInformationJoin
     */
    protected $categoryJoin;

    /**
     * @var CurrentStoreFetcher
     */
    protected $currentStoreFetcher;

    /**
     * @var PostInformationJoin
     */
    protected $postInformationJoin;

    /**
     * @var array
     */
    protected $columnsByTableRequiredIfNull = [];

    /**
     * @var DataModifier
     */
    protected $dataModifier;

    /**
     * @var DdlRegistry
     */
    protected $ddlRegistry;

    /**
     * Collection constructor.
     *
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param CategoryInformationJoin $categoryInformationJoin
     * @param CurrentStoreFetcher $currentStoreFetcher
     * @param null $connection
     * @param PostInformationJoin|null $postInformationJoin
     * @param DataModifier|null $dataModifier
     * @param DdlRegistry|null $ddlRegistry
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        CategoryInformationJoin $categoryInformationJoin,
        CurrentStoreFetcher $currentStoreFetcher,
        $connection = null,
        PostInformationJoin $postInformationJoin = null,
        DataModifier $dataModifier = null,
        DdlRegistry $ddlRegistry = null
    ) {
        $this->currentStoreFetcher = $currentStoreFetcher;
        $this->categoryJoin = $categoryInformationJoin;
        $this->postInformationJoin = $postInformationJoin ?:
            ObjectManager::getInstance()->get(PostInformationJoin::class);
        $this->dataModifier = $dataModifier ?:
            ObjectManager::getInstance()->get(DataModifier::class);
        $this->ddlRegistry = $ddlRegistry ?:
            ObjectManager::getInstance()->get(DdlRegistry::class);
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Digidirect\Blog\Model\Post', 'Digidirect\Blog\Model\ResourceModel\Post');
    }

    /**
     * @return mixed
     */
    protected function _afterLoad()
    {
        $this->loadRelatedCategories();
        $this->loadTags();
        $this->changeData();
        return parent::_afterLoad();
    }

    /**
     * @return $this
     */
    protected function changeData()
    {
        foreach ($this->getItems() as $item) {
            $data = $item->getData();
            $data = $this->dataModifier->modifyData($data, $this->postInformationJoin->getDefaultContentPrefix());
            $item->setData($data);
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function prepareDdl()
    {
        $array = [
            PostContentInterface::DIGIDIRECT_BLOG_POST_INFORMATION_TABLE,
        ];

        foreach ($array as $table) {
            $this->columnsByTableRequiredIfNull[$table] = $this->ddlRegistry->getTableColumns(
                $table,
                $this->getConnection()
            );
        }
        return $this;
    }

    /**
     * @return $this
     */
    protected function loadRelatedCategories()
    {
        $tableName = Post::CATEGORY_RELATION_TABLE;
        $linkField = 'post_id';
        $linkedIds = $this->getColumnValues('entity_id');
        if (!empty($linkedIds)) {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from(['rel' => $this->getTable($tableName)])
                ->joinLeft(
                    [Category::DIGIDIRECT_BLOG_CATEGORY_TABLE => $this->getTable(Category::DIGIDIRECT_BLOG_CATEGORY_TABLE)],
                    Category::DIGIDIRECT_BLOG_CATEGORY_TABLE . '.entity_id = rel.category_id'
                )
                ->where('rel.' . $linkField . ' IN (?)', $linkedIds);
            $this->categoryJoin->join(
                $select,
                $this->currentStoreFetcher->getCurrentStoreId()
            );

            $this->categoryJoin->joinDefault($select);
            $result = $connection->fetchAll($select);

            if ($result) {
                $categoryData = [];
                $categoryTitles = [];
                foreach ($result as $item) {
                    $categoryData[$item[$linkField]][] = $item['category_id'];
                    $categoryTitles[$item[$linkField]][] = $item['name'];
                }
                foreach ($this as $item) {
                    $linkedId = $item->getData('entity_id');
                    if (!isset($categoryData[$linkedId])) {
                        continue;
                    }

                    $item->setData('category_id', $categoryData[$linkedId]);
                    if (isset($categoryTitles[$linkedId])) {
                        $item->setData('categories_title', implode(', ', $categoryTitles[$linkedId]));
                    }
                }
            }
        }
        return $this;
    }

    /**
     * @return $this
     */
    protected function loadTags()
    {
        $linkedIds = $this->getColumnValues('entity_id');
        $storeId = $this->currentStoreFetcher->getCurrentStoreId();
        if (!empty($linkedIds)) {
            $result = $this->getTagsByPosts($linkedIds, $this->currentStoreFetcher->getCurrentStoreId());
            $tags = [];
            foreach ($result as $tagData) {
                $tags[$tagData['post_id']][] = $tagData['tag_name'];
            }
            if ($storeId != Store::DEFAULT_STORE_ID) {
                $linkedIds = array_diff($linkedIds, array_keys($tags));
                $result = $this->getTagsByPosts($linkedIds);
                foreach ($result as $tagData) {
                    $tags[$tagData['post_id']][] = $tagData['tag_name'];
                }
            }

            foreach ($this as $item) {
                if (isset($tags[$item->getId()])) {
                    $item->setData('tags', $tags[$item->getId()]);
                } else {
                    $item->setData('tags', []);
                }
            }
        }
        return $this;
    }

    /**
     * @param array $linkedIds
     * @param int $storeId
     * @param string $linkField
     * @return array
     */
    protected function getTagsByPosts($linkedIds, $storeId = Store::DEFAULT_STORE_ID, $linkField = 'post_id')
    {
        $tableName = Tag::TAG_POST_RELATION_TABLE;
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(['tag_relation' => $this->getTable($tableName)], ['tag_relation.post_id'])
            ->joinLeft(
                ['tag' => $this->getTable('digidirect_blog_tags')],
                'tag_relation.tag_id = tag.entity_id',
                ['tag_name' => 'name']
            )
            ->where('tag_relation.' . $linkField . ' IN (?)', $linkedIds)
            ->where('tag_relation.store_id = ?', $storeId);
        return $connection->fetchAll($select);
    }

    /**
     * @param array $stores
     * @param int $categoryStatus
     * @return $this
     */
    public function addFilterByCategoriesStore(array $stores, $categoryStatus = Status::STATUS_ENABLED)
    {
        $this->getSelect()
            ->joinInner(['category' => Post::CATEGORY_RELATION_TABLE], 'main_table.entity_id = category.post_id', [])
            ->joinInner(
                [Category::DIGIDIRECT_BLOG_CATEGORY_TABLE => $this->getTable(Category::DIGIDIRECT_BLOG_CATEGORY_TABLE)],
                'category.category_id = ' . Category::DIGIDIRECT_BLOG_CATEGORY_TABLE . '.entity_id',
                []
            )
            ->joinInner(['store' => Category::STORE_RELATION_TABLE], 'store.category_id = category.category_id', [])
            ->where('store.store_id IN (?)', $stores);

        $this->categoryJoin->join(
            $this->getSelect(),
            $this->currentStoreFetcher->getCurrentStoreId(),
            Category::DIGIDIRECT_BLOG_CATEGORY_TABLE,
            null,
            [CategoryContentInterface::CATEGORY_ID]
        );
        $this->categoryJoin->joinDefault(
            $this->getSelect(),
            Store::DEFAULT_STORE_ID,
            Category::DIGIDIRECT_BLOG_CATEGORY_TABLE,
            null,
            [CategoryContentInterface::CATEGORY_ID]
        );

        $statusExpr = Category::DIGIDIRECT_BLOG_CATEGORY_INFORMATION_TABLE . '.status =' . $categoryStatus;
        $expr = $this->getConnection()->getIfNullSql(
            $statusExpr,
            CategoryInformationJoin::DEFAULT_STORE_COLUMN_PREFIX . $statusExpr
        );

        $this->getSelect()->where(
            $expr
        );
        return $this;
    }

    /**
     * @param int $status
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addFilterByStatus($status = Status::STATUS_ENABLED)
    {
        if (!$this->currentStoreFetcher->getIsDefault()) {
            $statusExp = sprintf(
                PostContentInterface::DIGIDIRECT_BLOG_POST_INFORMATION_TABLE . '.status = "%s"',
                $status
            );
            $expr = $this->getConnection()->getIfNullSql(
                $statusExp,
                PostInformationJoin::DEFAULT_STORE_COLUMN_PREFIX . $statusExp
            );
            $this->getSelect()->where($expr);
            return $this;
        }
        $this->addFieldToFilter(
            PostContentInterface::DIGIDIRECT_BLOG_POST_INFORMATION_TABLE . '.' . PostInterface::FIELD_STATUS,
            $status
        );
        return $this;
    }

    /**
     * @param int $postId
     * @return $this
     */
    public function addPositionToSelect($postId)
    {
        $condition = new \Zend_Db_Expr('main_table.entity_id = rel.related_id AND rel.post_id = ' . (int)$postId);
        $this->getSelect()->joinLeft(['rel' => Post::RELATED_POST_TABLE], $condition, ['position']);
        return $this;
    }

    /**
     * @return $this
     */
    public function orderByPosition()
    {
        $this->getSelect()->order(['rel.position', 'main_table.entity_id']);
        return $this;
    }

    /**
     * @return $this
     */
    public function groupByEntityId()
    {
        $this->getSelect()->group('main_table.entity_id');
        return $this;
    }

    /**
     * @param int $postId
     * @return $this
     */
    public function addFilterByRelatedPost($postId)
    {
        $condition = new \Zend_Db_Expr('main_table.entity_id = rel.related_id');
        $this->getSelect()
            ->joinInner(['rel' => Post::RELATED_POST_TABLE], $condition, [])
            ->where('rel.post_id = ?', (int)$postId);
        return $this;
    }

    /**
     * @param int $tagId
     * @return $this
     */
    public function addFilterByTagId($tagId)
    {
        $storeId = $this->currentStoreFetcher->getCurrentStoreId();
        /** get all posts with current store configurations */
        $postsByCurrentStore = $this->selectPostTagsByCondition(['tag_relation.store_id' => ['eq' => $storeId]]);
        $defoultConditions = [
            'tag_relation.store_id' => ['eq' => Store::DEFAULT_STORE_ID],
            'tag_relation.tag_id' => ['eq' => $tagId]
        ];
        if (!empty($postsByCurrentStore)) {
            /** exclude posts for current store */
            $defoultConditions['tag_relation.post_id'] = ['nin' => $postsByCurrentStore];
        }
        $postsByDefaultStore = $this->selectPostTagsByCondition($defoultConditions);

        /** get post by tag id for current store */
        $postsByCurrentStore = $this->selectPostTagsByCondition([
            'tag_relation.store_id' => ['eq' => $storeId],
            'tag_relation.tag_id' => ['eq' => $tagId]
        ]);
        $postIds = array_merge($postsByCurrentStore, $postsByDefaultStore);
        $this->getSelect()->where('main_table.entity_id IN (?)', $postIds);
        return $this;
    }

    /**
     * @param array $conditions
     * @return array
     */
    public function selectPostTagsByCondition($conditions)
    {
        $tableName = Tag::TAG_POST_RELATION_TABLE;
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(['tag_relation' => $this->getTable($tableName)], ['tag_relation.post_id']);
        foreach ($conditions as $fieldName => $condition) {
            $condition = $connection->prepareSqlCondition($fieldName, $condition);
            $select->where($condition);
        }
        return $connection->fetchCol($select);
    }

    /**
     * @param string $year
     * @return $this
     */
    public function addFilterByYear($year)
    {
        $this->getSelect()->where('YEAR(publish_date) = ?', $year);
        return $this;
    }

    /**
     * @param string $month
     * @return $this
     */
    public function addFilterByMonth($month)
    {
        $this->getSelect()->where('MONTH(publish_date) = ?', $month);
        return $this;
    }

    /**
     * Add filter by posts
     *
     * @param array $postIds
     * @param bool $exclude
     * @return $this
     */
    public function addPostIdsFilter($postIds, $exclude = false)
    {
        $this->addFieldToFilter('main_table.entity_id', [$exclude ? 'nin' : 'in' => $postIds]);
        return $this;
    }

    /**
     * @param array|string $field
     * @param null $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($this->currentStoreFetcher->getIsDefault()) {
            parent::addFieldToFilter($field, $condition);
            return $this;
        }
        if (is_array($field)) {
            $conditions = [];
            foreach ($field as $key => $value) {
                $table = $this->getTableNameToSelect($value);
                $value = $this->getIfNullCondition($value, $table);
                $conditions[] = $this->_translateCondition($value, isset($condition[$key]) ? $condition[$key] : null);
            }

            $resultCondition = '(' . implode(') ' . \Magento\Framework\DB\Select::SQL_OR . ' (', $conditions) . ')';
        } else {
            $table = $this->getTableNameToSelect($field);
            $field = $this->getIfNullCondition($field, $table);
            $resultCondition = $this->_translateCondition($field, $condition);
        }

        $this->getSelect()->where($resultCondition, null, \Magento\Framework\DB\Select::TYPE_CONDITION);
        return $this;
    }

    /**
     * @param string $field
     * @param null $table
     * @return \Zend_Db_Expr
     */
    protected function getIfNullCondition($field, $table = null)
    {
        if (null === $table) {
            return $field;
        }
        $expr = $this->makeFieldSelectWithAlias($table, $field);
        return $this->getConnection()->getIfNullSql(
            $expr,
            CategoryInformationJoin::DEFAULT_STORE_COLUMN_PREFIX . $expr
        );
    }

    /**
     * @param string $alias
     * @param string $field
     * @return string
     */
    protected function makeFieldSelectWithAlias(string $alias, string $field): string
    {
        return $alias . '.' . $field;
    }

    /**
     * @param string $field
     * @return int|null|string
     */
    protected function getTableNameToSelect($field)
    {
        foreach ($this->columnsByTableRequiredIfNull as $tableName => $columns) {
            if (in_array($field, $columns)) {
                return $tableName;
            }
        }

        return null;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _initSelect()
    {
        $this->prepareDdl();
        parent::_initSelect();
        $this->postInformationJoin->join(
            $this->getSelect(),
            $this->currentStoreFetcher->getCurrentStoreId(),
            'main_table'
        );

        if (!$this->currentStoreFetcher->getIsDefault()) {
            $this->postInformationJoin->joinDefault(
                $this->getSelect(),
                $this->currentStoreFetcher->getDefaultStoreId(),
                'main_table'
            );
        }
        return $this;
    }
}
