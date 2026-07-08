<?php

namespace Digidirect\Blog\Model;

use Digidirect\Blog\Api\Data\PostInterface;
use Digidirect\Blog\Api\PostRepositoryInterface;
use Digidirect\Blog\Model\ResourceModel\Post\CollectionFactory;
use Digidirect\Blog\Model\ResourceModel\Post\Collection;
use Digidirect\Blog\Model\ResourceModel\Post;
use Digidirect\Blog\Model\Post as PostModel;
use Magento\Store\Model\Store;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Digidirect\Blog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Digidirect\Blog\Api\Data\PostContentInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PostRepository implements PostRepositoryInterface
{
    /**
     * @var Post
     */
    protected $resourceModel;

    /**
     * @var PostFactory
     */
    protected $modelFactory;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var PostModel[]
     */
    protected $postById = [];

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var Date
     */
    protected $dateFilter;

    /**
     * PostRepository constructor.
     *
     * @param Post $resourceModel
     * @param PostFactory $modelFactory
     * @param CollectionFactory $collectionFactory
     * @param DateTime $dateTime
     * @param ProductCollectionFactory $productCollectionFactory
     * @param TimezoneInterface $timezone
     * @param Date $date
     */
    public function __construct(
        Post $resourceModel,
        PostFactory $modelFactory,
        CollectionFactory $collectionFactory,
        DateTime $dateTime,
        ProductCollectionFactory $productCollectionFactory,
        TimezoneInterface $timezone,
        Date $date
    ) {
        $this->localeDate = $timezone;
        $this->resourceModel = $resourceModel;
        $this->modelFactory = $modelFactory;
        $this->collectionFactory = $collectionFactory;
        $this->dateTime = $dateTime;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->dateFilter = $date;
    }

    /**
     * @param int $id
     * @param int $storeId
     * @return \Digidirect\Blog\Model\Post
     */
    public function getById($id, $storeId = null)
    {
        $cacheKey = implode('_', [$id, $storeId]);
        if (!isset($this->postById[$cacheKey])) {
            $item = $this->modelFactory->create();
            $this->resourceModel->loadById($item, $id, $storeId);
            $this->postById[$cacheKey] = $item;
        }

        return $this->postById[$cacheKey];
    }

    /**
     * @param array $ids
     * @param int $status
     * @return int
     */
    public function updateStatus(array $ids, $status)
    {
        return $this->resourceModel->updateStatus($ids, $status);
    }

    /**
     * @param int $id
     * @return $this
     * @throws \Exception
     */
    public function deleteById($id)
    {
        return $this->resourceModel->delete($this->getById($id));
    }

    /**
     * @param \Digidirect\Blog\Model\Post $item
     * @param null|array $relatedPosts
     * @param null|array $relatedProducts
     * @return \Digidirect\Blog\Model\Post
     * @throws \Exception
     */
    public function save(\Digidirect\Blog\Model\Post $item, $relatedPosts = null, $relatedProducts = null)
    {
        $date = $item->getData(PostInterface::FIELD_PUBLISH_DATE);
        $date = $this->dateFilter->filter($date);
        $item->setData(PostInterface::FIELD_PUBLISH_DATE, $date);
        $item->setData(PostInterface::FIELD_UPDATED_AT, $this->dateTime->gmtDate());
        $this->resourceModel->save($item);
        $this->resourceModel->updateCategories($item->getId(), $item->getCategoryId());
        $this->resourceModel->updateTags(
            $item->getId(),
            $item->getTags(),
            $item->getData(PostContentInterface::STORE_ID)
        );
        if (is_array($relatedPosts)) {
            $this->resourceModel->updateRelatedPosts($item->getId(), $relatedPosts);
        }
        if (is_array($relatedProducts)) {
            $this->resourceModel->updateRelatedProducts($item->getId(), $relatedProducts);
        }
        return $item;
    }

    /**
     * @param string $urlKey
     * @param int $storeId
     * @return \Magento\Framework\DB\Select
     */
    public function getPostIdByUrlKey($urlKey, $storeId)
    {
        return $this->resourceModel->loadPostIdByUrlKey($urlKey, $storeId);
    }

    /**
     * @param int $status
     * @param array|int $store
     * @param string $publishDate
     * @param string $publishDateCondition
     * @return Collection
     */
    public function getPostList($status, $store, $publishDate = '', $publishDateCondition = 'lteq')
    {
        if (!is_array($store)) {
            $store = [$store];
        }
        if (!in_array(Store::DEFAULT_STORE_ID, $store)) {
            $store[] = Store::DEFAULT_STORE_ID;
        }
        if (empty($publishDate)) {
            $publishDate = $this->dateTime->date();
        }
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFilterByStatus($status);
        $collection->addFieldToFilter(PostInterface::FIELD_PUBLISH_DATE, [$publishDateCondition => $publishDate]);
        $collection->addFilterByCategoriesStore($store);
        $collection->getSelect()->group('main_table.entity_id');
        return $collection;
    }

    /**
     * @param PostInterface $post
     * @return void
     */
    public function updateView(PostInterface $post)
    {
        $count = $post->getViews();
        $this->resourceModel->updatePostData($post->getId(), ['views' => ++$count]);
    }

    /**
     * @param int $postId
     * @return array
     */
    public function getPostTags($postId)
    {
        return $this->resourceModel->lookupTags($postId);
    }

    /**
     * @param int $postId
     * @return array
     */
    public function getRelatedPostIds($postId)
    {
        return $this->resourceModel->loadRelatedPostIds($postId);
    }

    /**
     * @param int $postId
     * @return array
     */
    public function getRelatedProductsIds($postId)
    {
        return $this->resourceModel->loadRelatedProductIds($postId);
    }

    /**
     * @param int $postId
     * @param bool $assignedOnly
     * @return ResourceModel\Product\Collection
     */
    public function getRelatedProducts($postId, $assignedOnly = true)
    {
        $collection = $this->productCollectionFactory->create();
        if (!empty($postId)) {
            $joinName = ['rel' => \Digidirect\Blog\Model\ResourceModel\Post::RELATED_PRODUCTS_TABLE];
            $expression = new \Zend_Db_Expr('rel.product_id = e.entity_id AND rel.post_id = ' . $postId);
            $joinColumns = ['position' => 'rel.position'];
            if ($assignedOnly === true) {
                $collection->getSelect()->joinInner($joinName, $expression, $joinColumns);
            } else {
                $collection->getSelect()->joinLeft($joinName, $expression, $joinColumns);
            }
            $collection->getSelect()->order('rel.position ASC');
        }
        return $collection;
    }

    /**
     * @param int $postId
     * @return int
     */
    public function getCategoryId($postId)
    {
        return $this->resourceModel->loadCategoryId($postId);
    }

    /**
     * @param int $postId
     * @param bool $activeOnly
     * @return array
     */
    public function getCategories($postId, $activeOnly = false)
    {
        return $this->resourceModel->lookupCategories($postId, $activeOnly);
    }
}
