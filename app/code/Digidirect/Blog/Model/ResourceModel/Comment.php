<?php
namespace Digidirect\Blog\Model\ResourceModel;

/**
 * Class Comment
 */
class Comment extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('digidirect_blog_comment', 'entity_id');
    }

    /**
     * @param array $ids
     * @param int $status
     * @return int
     */
    public function updateStatus(array $ids, $status)
    {
        $connection = $this->getConnection();
        return $connection->update(
            $this->getMainTable(),
            [
                'comment_status' => $status,
            ],
            $connection->quoteInto('entity_id IN (?)', $ids)
        );
    }

    /**
     * @param int $postId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCountByPostId($postId)
    {
        $connection = $this->getConnection();
        $select = $connection
            ->select()
            ->from(['main_table' => $this->getMainTable()], new \Zend_Db_Expr('COUNT(main_table.entity_id)'))
            ->where('post_id = ?', (int)$postId);
        return $connection->fetchOne($select);
    }
}
