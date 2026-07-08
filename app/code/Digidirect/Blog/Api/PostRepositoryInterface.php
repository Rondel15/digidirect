<?php
namespace Digidirect\Blog\Api;

use Digidirect\Blog\Api\Data\PostInterface;
use Digidirect\Blog\Model\ResourceModel\Post\Collection;

/**
 * Interface PostRepositoryInterface
 */
interface PostRepositoryInterface extends AbstractRepositoryInterface
{
    /**
     * @param \Digidirect\Blog\Model\Post $item
     * @return mixed
     */
    public function save(\Digidirect\Blog\Model\Post $item);

    /**
     * Update status
     *
     * @param array $ids
     * @param int $status
     * @return int
     */
    public function updateStatus(array $ids, $status);

    /**
     * @param string $urlKey
     * @param int $storeId
     * @return \Magento\Framework\DB\Select
     */
    public function getPostIdByUrlKey($urlKey, $storeId);

    /**
     * @param int $status
     * @param int|array $store
     * @param string $publishDate
     * @param string $publishDateCondition
     * @return Collection
     */
    public function getPostList($status, $store, $publishDate = '', $publishDateCondition = 'lteq');

    /**
     * @param PostInterface $post
     * @return void
     */
    public function updateView(PostInterface $post);

    /**
     * @param int $postId
     * @return array
     */
    public function getPostTags($postId);

    /**
     * @param int $postId
     * @return array
     */
    public function getRelatedPostIds($postId);

    /**
     * @param int $postId
     * @return array
     */
    public function getRelatedProductsIds($postId);

    /**
     * @param int $postId
     * @param bool $assignedOnly
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getRelatedProducts($postId, $assignedOnly = true);

    /**
     * @param int $postId
     * @return int
     */
    public function getCategoryId($postId);
}
