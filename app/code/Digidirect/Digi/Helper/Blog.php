<?php
namespace Digidirect\Digi\Helper;

use \Magento\Framework\Exception\LocalizedException;

/**
 * Class Blog
 * @package Digidirect\Digi\Helper
 */
class Blog extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CATEGORY_RELATION_TABLE = 'digidirect_blog_post_categories';

    const CATEGORY_TABLE = 'digidirect_blog_category_information';

    const TOP_NAME = 'Top Contributors';

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Blog constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->connection = $resource->getConnection();
        parent::__construct($context);
    }

    /**
     * @param null|\Digidirect\Blog\Api\Data\PostInterface $post
     * @return bool
     */
    public function isPostRelatedToContributors($post = null)
    {
        if (empty($post) || !($post instanceof \Digidirect\Blog\Api\Data\PostInterface)) {
            $post = $this->_coreRegistry->registry(\Digidirect\Blog\Api\Data\PostInterface::CURRENT_ITEM);
        }

        $select = $this->connection->select()
            ->from(['rel' => self::CATEGORY_RELATION_TABLE], 'category_id')
            ->joinLeft(['cat' => self::CATEGORY_TABLE], 'cat.information_category_id = rel.category_id', [])
            ->where('rel.post_id = :post_id AND cat.name = :cat_name')
            ->limit(1);

        return (bool) $this->connection->fetchOne(
            $select,
            [
                'post_id' => (int)$post->getId(),
                'cat_name' => self::TOP_NAME
            ]
        );
    }
}
