<?php

namespace Digidirect\Blog\Model\ResourceModel;

use Digidirect\Blog\Api\Data\PostInterface;
use Digidirect\Blog\Model\Config\Provider\Status;
use Digidirect\Blog\Model\CurrentStoreFetcher;
use Digidirect\Blog\Sql\Aliases;
use Digidirect\Blog\Sql\CategoryInformationJoin;
use Digidirect\Blog\Sql\PostInformationJoin;
use Digidirect\Blog\Sql\PostInformationSave;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Digidirect\Blog\Api\Data\PostContentInterface;
use Magento\Store\Model\Store;

/**
 * Class Post
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Post extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const CATEGORY_RELATION_TABLE = 'digidirect_blog_post_categories';
    const RELATED_POST_TABLE = 'digidirect_blog_post_related_post';
    const RELATED_PRODUCTS_TABLE = 'digidirect_blog_post_related_products';
    const BLOG_POST_INFORMATION_TABLE = 'digidirect_blog_post_information';
    const CATEGORY_STORE_TABLE = 'digidirect_blog_category_stores';

    /**
     * @var Tag
     */
    protected $tagResource;

    /**
     * @var CategoryInformationJoin
     */
    protected $categoryJoin;

    /**
     * @var CurrentStoreFetcher
     */
    protected $currentStoreFetcher;

    /**
     * @var PostInformationSave
     */
    protected $savePostInformation;

    /**
     * @var PostInformationJoin
     */
    protected $postJoin;

    /**
     * Post constructor.
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Digidirect\Blog\Model\ResourceModel\Tag $tagResource
     * @param CategoryInformationJoin $categoryInformationJoin
     * @param CurrentStoreFetcher $currentStoreFetcher
     * @param null $connectionName
     * @param PostInformationSave $postInformationSave
     * @param PostInformationJoin $postJoin
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        Tag $tagResource,
        CategoryInformationJoin $categoryInformationJoin,
        CurrentStoreFetcher $currentStoreFetcher,
        $connectionName = null,
        PostInformationSave $postInformationSave = null,
        PostInformationJoin $postJoin = null
    ) {
        $this->currentStoreFetcher = $currentStoreFetcher;
        $this->categoryJoin = $categoryInformationJoin;
        parent::__construct($context, $connectionName);
        $this->tagResource = $tagResource;
        $this->savePostInformation = $postInformationSave ?:
            ObjectManager::getInstance()->get(PostInformationSave::class);
        $this->postJoin = $postJoin ?:
            ObjectManager::getInstance()->get(PostInformationJoin::class);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(PostInterface::DIGIDIRECT_BLOG_POST_TABLE, 'entity_id');
    }

    /**
     * @param array $ids
     * @param int $status
     * @return int
     * @throws LocalizedException
     */
    public function updateStatus(array $ids, $status)
    {
        $connection = $this->getConnection();
        return $connection->update(
            $this->getTable(PostContentInterface::DIGIDIRECT_BLOG_POST_INFORMATION_TABLE),
            [
                'status' => $status,
            ],
            $connection->quoteInto('information_post_id IN (?) AND information_store_id = 0', $ids)
        );
    }

    /**
     * @param string $postId
     * @param array $newCategories
     * @return void
     */
    public function updateCategories($postId, array $newCategories)
    {
        $connection = $this->getConnection();
        $oldCategories = $this->lookupCategoryIds((int)$postId);
        $table = $this->getTable(self::CATEGORY_RELATION_TABLE);
        $delete = array_diff($oldCategories, $newCategories);

        if ($delete) {
            $where = [
                'post_id = ?' => (int)$postId,
                'category_id IN (?)' => $delete,
            ];
            $connection->delete($table, $where);
        }

        $insert = array_diff($newCategories, $oldCategories);
        if ($insert) {
            $data = [];
            foreach ($insert as $categoryId) {
                $data[] = [
                    'post_id' => (int)$postId,
                    'category_id' => (int)$categoryId,
                ];
            }
            $connection->insertMultiple($table, $data);
        }
    }

    /**
     * Get category ids to which specified item is assigned
     *
     * @param int $postId
     * @return array
     */
    public function lookupCategoryIds($postId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(['relation' => self::CATEGORY_RELATION_TABLE], 'category_id')
            ->where('relation.post_id = :post_id');

        return $connection->fetchCol($select, ['post_id' => (int)$postId]);
    }

    /**
     * Get fist category id to which specified item is assigned
     *
     * @param int $postId
     * @return mixed
     */
    public function loadCategoryId($postId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(['relation' => self::CATEGORY_RELATION_TABLE], 'category_id')
            ->where('relation.post_id = :post_id');

        return $connection->fetchOne($select, ['post_id' => (int)$postId]);
    }

    /**
     * @param int $postId
     * @param bool $activeOnly
     * @return array
     * @throws LocalizedException
     */
    public function lookupCategories($postId, $activeOnly = false)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(['post_category' => $this->getTable(Post::CATEGORY_RELATION_TABLE)], 'category_id')
            ->join(
                ['post' => $this->getMainTable()],
                'post_category.post_id = post.entity_id',
                []
            )
            ->join(
                ['category' => $this->getTable(Category::DIGIDIRECT_BLOG_CATEGORY_TABLE)],
                'post_category.category_id = category.entity_id',
                ['category_name' => 'name', 'category_status' => 'category.status']
            )
            ->where('post.entity_id = :post_id');

        $storeId = $this->currentStoreFetcher->getCurrentStoreId();
        if (!$this->categoryJoin->hasStoreViewContent($postId, $storeId)) {
            $storeId = $this->currentStoreFetcher->getDefaultStoreId();
        }
        $this->categoryJoin->join(
            $select,
            $storeId,
            null,
            $postId
        );

        if ($activeOnly) {
            $select->where(
                Category::DIGIDIRECT_BLOG_CATEGORY_INFORMATION_TABLE . '.status = ?',
                Status::STATUS_ENABLED
            );
        }

        return $connection->fetchPairs($select, ['post_id' => (int)$postId]);
    }

    /**
     * @param int $postId
     * @param string $newTags
     * @param int $storeId
     * @throws LocalizedException
     * @return void
     */
    public function updateTags($postId, $newTags, $storeId)
    {
        $connection = $this->getConnection();

        $oldTags = $this->lookupTags($postId, $storeId);
        if (!is_array($newTags)) {
            $newTags = explode(',', $newTags);
        }
        $newTags = array_map('trim', $newTags);
        $existsTags = $this->tagResource->getTagsByName($newTags);
        $needCreateTags = array_diff($newTags, $existsTags);
        if (!empty($needCreateTags)) {
            $table = $this->tagResource->getMainTable();
            $data = [];
            foreach ($needCreateTags as $newTag) {
                $newTag = trim($newTag);
                if (!empty($newTag)) {
                    $data[] = [
                        'name' => $newTag
                    ];
                }
            }
            if (!empty($data)) {
                $connection->insertMultiple($table, $data);
            }
            $existsTags = $this->tagResource->getTagsByName($newTags);
        }
        $table = $this->getTable(Tag::TAG_POST_RELATION_TABLE);
        $delete = array_keys(array_diff($oldTags, $newTags));
        if ($delete) {
            $where = [
                'post_id = ?' => (int)$postId,
                'tag_id IN (?)' => $delete,
                'store_id = ?' => (int)$storeId
            ];
            $connection->delete($table, $where);
        }
        $insert = array_keys(array_diff($existsTags, $oldTags));
        if ($insert) {
            $data = [];
            foreach ($insert as $tagId) {
                $data[] = [
                    'post_id' => (int)$postId,
                    'tag_id' => (int)$tagId,
                    'store_id' => (int)$storeId
                ];
            }
            $connection->insertMultiple($table, $data);
        }
    }

    /**
     * @param array|int $postId
     * @param int $storeId
     * @return array
     * @throws LocalizedException
     */
    public function lookupTags($postId, $storeId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(['ptr' => $this->getTable(Tag::TAG_POST_RELATION_TABLE)], 'tag_id')
            ->join(
                ['p' => $this->getMainTable()],
                'ptr.post_id = p.entity_id',
                []
            )
            ->join(['tag' => $this->getTable('digidirect_blog_tags')], 'ptr.tag_id = tag.entity_id', ['tag_name' => 'name'])
            ->where('p.entity_id = :post_id')
            ->where('ptr.store_id = ?', $storeId);
        return $connection->fetchPairs($select, ['post_id' => (int)$postId]);
    }

    /**
     * @param string $urlKey
     * @param int $storeId
     * @param int $status
     * @param int $categoryStatus
     * @return mixed
     * @throws LocalizedException
     */
    public function loadPostIdByUrlKey(
        $urlKey,
        $storeId,
        $status = Status::STATUS_ENABLED,
        $categoryStatus = Status::STATUS_ENABLED
    ) {
        $select = $this->getConnection()
            ->select()
            ->from(['posts' => $this->getMainTable()])
            ->joinInner(
                ['cat_rel' => $this->getTable(self::CATEGORY_RELATION_TABLE)],
                'posts.entity_id = cat_rel.post_id'
            );
        $this->postJoin->join($select, $storeId, 'posts');

        $storeIds = [$storeId];
        if (!in_array(Store::DEFAULT_STORE_ID, $storeIds)) {
            $storeIds[] = Store::DEFAULT_STORE_ID;
        }
        $select->joinInner(
            [Aliases::CATEGORY_ENTITY_TABLE_ALIAS => $this->getTable(Category::DIGIDIRECT_BLOG_CATEGORY_TABLE)],
            'cat_rel.category_id = ' . Aliases::CATEGORY_ENTITY_TABLE_ALIAS . '.entity_id'
        )
            ->joinInner(
                ['cat_stores' => $this->getTable(Category::STORE_RELATION_TABLE)],
                'cat_stores.category_id = cat_rel.category_id'
            )
            ->where('cat_stores.store_id IN (?)', $storeIds);

        $urlKeyExp = sprintf(PostContentInterface::DIGIDIRECT_BLOG_POST_INFORMATION_TABLE . '.url_key = "%s"', $urlKey);
        $statusExp = sprintf(PostContentInterface::DIGIDIRECT_BLOG_POST_INFORMATION_TABLE . '.status = "%s"', $status);
        if ($storeId != Store::DEFAULT_STORE_ID) {
            $this->postJoin->joinDefault($select, Store::DEFAULT_STORE_ID, 'posts');
            $urlKeyExp = $this->getConnection()->getIfNullSql(
                $urlKeyExp,
                PostInformationJoin::DEFAULT_STORE_COLUMN_PREFIX . $urlKeyExp
            );
            $statusExp = $this->getConnection()->getIfNullSql(
                $statusExp,
                PostInformationJoin::DEFAULT_STORE_COLUMN_PREFIX . $statusExp
            );
        }

        $select->where($urlKeyExp);

        $this->categoryJoin->join(
            $select,
            $this->currentStoreFetcher->getCurrentStoreId()
        );

        $this->categoryJoin->joinDefault($select);

        if ($status !== null) {
            $select->where($statusExp);
        }
        if ($categoryStatus !== null) {
            $this->categoryJoin->processIfNull('status', $categoryStatus);
        }
        return $this->getConnection()->fetchOne($select);
    }

    /**
     * @param int $postId
     * @param array $data
     * @return int
     * @throws LocalizedException
     */
    public function updatePostData($postId, array $data)
    {
        return $this->getConnection()->update($this->getMainTable(), $data, ['entity_id = ?' => (int)$postId]);
    }

    /**
     * @param int $postId
     * @return array
     */
    public function loadRelatedPostIds($postId)
    {
        return $this->loadRelatedIds($postId, self::RELATED_POST_TABLE, 'related_id');
    }

    /**
     * @param int $postId
     * @return array
     */
    public function loadRelatedProductIds($postId)
    {
        return $this->loadRelatedIds($postId, self::RELATED_PRODUCTS_TABLE, 'product_id');
    }

    /**
     * @param int $postId
     * @param array $relatedData
     * @return bool
     */
    public function updateRelatedPosts($postId, array $relatedData)
    {
        return $this->saveRelatedData(
            $postId,
            self::RELATED_POST_TABLE,
            'related_id',
            $relatedData,
            $this->loadRelatedPostIds($postId)
        );
    }

    /**
     * @param int $postId
     * @param array $relatedData
     * @return bool
     */
    public function updateRelatedProducts($postId, array $relatedData)
    {
        return $this->saveRelatedData(
            $postId,
            self::RELATED_PRODUCTS_TABLE,
            'product_id',
            $relatedData,
            $this->loadRelatedProductIds($postId)
        );
    }

    /**
     * @param int $postId
     * @param string $table
     * @param string $field
     * @return array
     */
    protected function loadRelatedIds($postId, $table, $field)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(['relation' => $this->getTable($table)], [$field, 'position'])
            ->where('relation.post_id = :post_id');
        return $connection->fetchPairs($select, ['post_id' => (int)$postId]);
    }

    /**
     * @param int $postId
     * @param string $table
     * @param string $field
     * @param array $relatedData
     * @param array $currentRelations
     * @return bool
     */
    protected function saveRelatedData($postId, $table, $field, array $relatedData, array $currentRelations)
    {
        $connection = $this->getConnection();
        if (empty($relatedData)) {
            $connection->delete($table, ['post_id = ?' => $postId]);
            $dataWasChanged = true;
        } else {
            $needDeleteIds = array_diff_key($currentRelations, $relatedData);
            foreach ($relatedData as $relatedId => $position) {
                $relatedId = (int)$relatedId;
                if (empty($relatedId)) {
                    continue;
                }
                $saveData = [
                    'post_id' => (int)$postId,
                    $field => $relatedId,
                    'position' => (int)$position,
                ];
                $connection->insertOnDuplicate($table, $saveData, ['position']);
            }
            $dataWasChanged = true;
            if (!empty($needDeleteIds)) {
                $connection->delete(
                    $table,
                    ['post_id = ?' => $postId, $field . ' IN(?)' => array_keys($needDeleteIds)]
                );
                $dataWasChanged = true;
            }
        }
        return $dataWasChanged;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this|\Magento\Framework\Model\ResourceModel\Db\AbstractDb
     * @throws LocalizedException
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_afterSave($object);
        $this->savePostInformation->saveInformation($object);
        return $this;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param int $id
     * @param int $storeId
     * @return $this
     * @throws LocalizedException
     */
    public function loadById($object, $id, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->currentStoreFetcher->getCurrentStoreId();
        }

        $select = $this->getConnection()
            ->select()
            ->from(['posts' => $this->getMainTable()])
            ->where('posts.entity_id = ?', $id);

        $this->postJoin->join(
            $select,
            $storeId,
            'posts',
            $id
        );

        $result = $this->getConnection()->fetchRow($select);
        $object->setData($result);
        return $this;
    }

    /**
     * @param string|int $postId
     * @return array
     */
    public function getStoreRelationCategoryByPostId($postId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(['post_category'
            => $this->getTable(Post::CATEGORY_RELATION_TABLE)], 'category_store.store_id')
            ->joinLeft(
                ['category_store' => self::CATEGORY_STORE_TABLE],
                'post_category.category_id = category_store.category_id',
                []
            )
            ->where('post_category.post_id = :post_id');

        return $connection->fetchAssoc($select, ['post_id' => (int)$postId]);
    }
}
