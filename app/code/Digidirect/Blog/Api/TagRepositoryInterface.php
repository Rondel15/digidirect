<?php
namespace Digidirect\Blog\Api;

use Digidirect\Blog\Model\ResourceModel\Tag\Collection;

/**
 * Interface TagRepositoryInterface
 */
interface TagRepositoryInterface
{
    /**
     * @param int $storeId
     * @return Collection
     */
    public function getRandomTags($storeId);

    /**
     * @param string $tagName
     * @return \Digidirect\Blog\Model\Tag
     */
    public function getByName($tagName);

    /**
     * @param int $postId
     * @return Collection
     */
    public function getTagsByPostId($postId);
}
