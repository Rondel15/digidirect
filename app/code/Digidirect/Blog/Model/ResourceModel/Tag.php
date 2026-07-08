<?php
namespace Digidirect\Blog\Model\ResourceModel;

use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Tag
 */
class Tag extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const TAG_POST_RELATION_TABLE = 'digidirect_blog_post_tags';

    /**
     * Tag constructor.
     * @param Context $context
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
    }

    /**
     *
     */
    protected function _construct()
    {
        $this->_init('digidirect_blog_tags', 'entity_id');
    }

    /**
     * @param array $tags
     * @return array
     */
    public function getTagsByName(array $tags)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            ['tags' => $this->getMainTable()]
        )->where('tags.name in (?)', $tags);
        return $connection->fetchPairs($select);
    }
}
