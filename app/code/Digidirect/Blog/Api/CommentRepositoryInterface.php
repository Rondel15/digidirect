<?php
namespace Digidirect\Blog\Api;

use Digidirect\Blog\Model\Comment\Source\Status;
use Digidirect\Blog\Model\ResourceModel\Comment\Collection;

/**
 * Interface PostRepositoryInterface
 */
interface CommentRepositoryInterface extends AbstractRepositoryInterface
{
    /**
     * @param \Digidirect\Blog\Model\Comment $item
     * @return mixed
     */
    public function save(\Digidirect\Blog\Model\Comment $item);

    /**
     * Update status
     *
     * @param array $ids
     * @param int $status
     * @return int
     */
    public function updateStatus(array $ids, $status);

    /**
     * @param int $postId
     * @return string
     */
    public function getCountByPostId($postId);

    /**
     * @param int $postId
     * @param int $status
     * @return Collection
     */
    public function getCommentsList($postId, $status = Status::STATUS_APPROVED);
}
