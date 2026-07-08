<?php

namespace Digidirect\Blog\Model\ResourceModel;

use Digidirect\Blog\Api\Data\PostInterface;
use Digidirect\Blog\Model\Config\Provider\Status;
use Digidirect\Blog\Model\CurrentStoreFetcher;
use Digidirect\Blog\Sql\Aliases;
use Digidirect\Blog\Sql\CategoryInformationJoin;
use Digidirect\Blog\Sql\CategoryInformationSave;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Digidirect\Blog\Sql\PostInformationJoin;
use Magento\Framework\App\ObjectManager;
use Digidirect\Blog\Api\Data\PostContentInterface;
use Magento\Store\Model\Store;

/**
 * Class Category
 */
class Category extends AbstractDb
{
    const STORE_RELATION_TABLE = 'digidirect_blog_category_stores';
    const DIGIDIRECT_BLOG_CATEGORY_TABLE = 'digidirect_blog_category';
    const DIGIDIRECT_BLOG_CATEGORY_INFORMATION_TABLE = 'digidirect_blog_category_information';

    /**
     * @var CurrentStoreFetcher
     */
    protected $storeFetcher;

    /**
     * @var CategoryInformationJoin
     */
    protected $joinStoreInformation;

    /**
     * @var CategoryInformationSave
     */
    protected $saveStoreInformation;

    /**
     * @var PostInformationJoin
     */
    protected $postJoin;

    /**
     * Category constructor.
     * @param Context $context
     * @param CurrentStoreFetcher $currentStoreFetcher
     * @param CategoryInformationJoin $categoryInformationJoin
     * @param CategoryInformationSave $categoryInformationSave
     * @param null $connectionName
     * @param PostInformationJoin|null $postJoin
     */
    public function __construct(
        Context $context,
        CurrentStoreFetcher $currentStoreFetcher,
        CategoryInformationJoin $categoryInformationJoin,
        CategoryInformationSave $categoryInformationSave,
        $connectionName = null,
        PostInformationJoin $postJoin = null
    ) {
        $this->saveStoreInformation = $categoryInformationSave;
        $this->joinStoreInformation = $categoryInformationJoin;
        $this->storeFetcher = $currentStoreFetcher;
        $this->postJoin = $postJoin ?:
            ObjectManager::getInstance()->get(PostInformationJoin::class);
        parent::__construct($context, $connectionName);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::DIGIDIRECT_BLOG_CATEGORY_TABLE, 'entity_id');
    }

    /**
     * @param string $categoryId
     * @param array $newStores
     * @return void
     */
    public function updateStores($categoryId, array $newStores)
    {
        $connection = $this->getConnection();
        $oldStores = $this->lookupStoreIds((int)$categoryId);
        $table = $this->getTable(self::STORE_RELATION_TABLE);
        $delete = array_diff($oldStores, $newStores);

        if ($delete) {
            $where = [
                'category_id = ?' => (int)$categoryId,
                'store_id IN (?)' => $delete,
            ];
            $connection->delete($table, $where);
        }

        $insert = array_diff($newStores, $oldStores);
        if ($insert) {
            $data = [];
            foreach ($insert as $storeId) {
                $data[] = [
                    'category_id' => (int)$categoryId,
                    'store_id' => (int)$storeId,
                ];
            }
            $connection->insertMultiple($table, $data);
        }
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $categoryId
     * @return array
     */
    public function lookupStoreIds($categoryId)
    {
        $select = $this->getStoreRelationSelect();
        $select->where(Aliases::CATEGORY_ENTITY_TABLE_ALIAS . '.entity_id = :category_id');
        return $this->getConnection()->fetchCol($select, ['category_id' => (int)$categoryId]);
    }

    /**
     * @param string $urlKey
     * @param array $storeId
     * @return string|bool
     */
    public function loadCategoryIdByUrlKey($urlKey, array $storeId)
    {
        $connection = $this->getConnection();
        $select = $this->getStoreRelationSelect();
        $select->reset(\Zend_Db_Select::COLUMNS)->columns('category_id');
        $urlKeyExpr = sprintf(Category::DIGIDIRECT_BLOG_CATEGORY_INFORMATION_TABLE . '.url_key = "%s"', $urlKey);
        $expr = $this->getConnection()->getIfNullSql(
            $urlKeyExpr,
            CategoryInformationJoin::DEFAULT_STORE_COLUMN_PREFIX . $urlKeyExpr
        );
        $select->where(
            $expr
        );
        $select->where('bcs.store_id IN (?)', $storeId);
        return $connection->fetchOne($select);
    }

    /**
     * @return \Magento\Framework\DB\Select
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getStoreRelationSelect()
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(['bcs' => self::STORE_RELATION_TABLE], 'store_id')
            ->join(
                [Aliases::CATEGORY_ENTITY_TABLE_ALIAS => $this->getMainTable()],
                'bcs.category_id =' . Aliases::CATEGORY_ENTITY_TABLE_ALIAS . ' .entity_id',
                [Aliases::CATEGORY_ENTITY_TABLE_ALIAS . '.entity_id']
            );
        $this->joinStoreInformation->join($select, $this->storeFetcher->getCurrentStoreId());
        $this->joinStoreInformation->joinDefault($select);
        return $select;
    }

    /**
     * @param int $categoryId
     * @param int $storeId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCountPostsByCategoryId($categoryId, $storeId)
    {
        $connection = $this->getConnection();
        $select = $connection
            ->select()
            ->from(['rel' => Post::CATEGORY_RELATION_TABLE], new \Zend_Db_Expr('COUNT(rel.post_id)'))
            ->joinLeft(
                ['post' => $this->getTable(PostInterface::DIGIDIRECT_BLOG_POST_TABLE)],
                'rel.post_id = post.entity_id',
                []
            );

        $this->postJoin->join($select, $storeId, 'post');
        $statusExp = sprintf(
            PostContentInterface::DIGIDIRECT_BLOG_POST_INFORMATION_TABLE . '.status = "%s"',
            Status::STATUS_ENABLED
        );
        if ($storeId != Store::DEFAULT_STORE_ID) {
            $this->postJoin->joinDefault($select, Store::DEFAULT_STORE_ID, 'post');
            $statusExp = $this->getConnection()->getIfNullSql(
                $statusExp,
                PostInformationJoin::DEFAULT_STORE_COLUMN_PREFIX . $statusExp
            );
        }
        $select->where($statusExp)
            ->where('post.publish_date <= NOW()')
            ->where('rel.category_id = ?', (int)$categoryId);
        return $connection->fetchOne($select);
    }

    /**
     * @param int $id
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadById($id)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getMainTable())->where('entity_id = ?', (int)$id);
        return $connection->fetchRow($select);
    }

    /**
     * @param int $categoryId
     * @param int $parentId
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateParentId($categoryId, $parentId)
    {
        return $this->getConnection()->update(
            $this->getMainTable(),
            ['parent_id' => $parentId],
            ['parent_id = ?' => $categoryId]
        );
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\DB\Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $storeId = $this->storeFetcher->getCurrentStoreId();
        $select = $this->joinStoreInformation->join($select, $storeId, $this->getMainTable(), $value);
        return $select;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_afterSave($object);
        $this->saveStoreInformation->saveInformation($object);
        return $this;
    }
}
