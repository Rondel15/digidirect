<?php
namespace Digidirect\Blog\Setup;

use Digidirect\Blog\Api\Data\PostInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $this->createBlogCategory($installer);
        $this->createBlogPost($installer);
        $this->createBlogComment($installer);
        $this->createRelatedProduct($installer);
        $this->createRelatedPost($installer);
        $this->createTag($installer);

        $installer->endSetup();
    }

    /**
     * @param SchemaSetupInterface $installer
     * @return $this
     * @throws \Zend_Db_Exception
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function createBlogCategory(SchemaSetupInterface $installer)
    {
        $tableCat = $installer->getConnection()->newTable(
            $installer->getTable('digidirect_blog_category')
        )->addColumn(
            'entity_id',
            Table::TYPE_INTEGER,
            10,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Category ID'
        )->addColumn(
            'parent_id',
            Table::TYPE_INTEGER,
            10,
            ['unsigned' => true],
            'Category Parent'
        )->addColumn(
            'name',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Category Name'
        )->addColumn(
            'status',
            Table::TYPE_SMALLINT,
            null,
            [],
            'Category Status'
        )->addColumn(
            'url_key',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Category URL Key'
        )->addColumn(
            'order',
            Table::TYPE_SMALLINT,
            null,
            [],
            'Category Sort Order'
        )->addColumn(
            'meta_title',
            Table::TYPE_TEXT,
            '2M',
            ['nullable' => false],
            'Meta Keywords'
        )->addColumn(
            'meta_keywords',
            Table::TYPE_TEXT,
            '2M',
            ['nullable' => false],
            'Meta Keywords'
        )->addColumn(
            'meta_description',
            Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'Meta Description'
        )->setComment(
            'Category Table'
        );

        $installer->getConnection()->createTable($tableCat);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('digidirect_blog_category_stores')
        )->addColumn(
            'category_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['nullable' => false, 'unsigned' => true, 'primary' => true],
            'Category Id'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            5,
            ['nullable' => false, 'unsigned' => true, 'primary' => true],
            'Store Id'
        )->setComment(
            'Stores for posts'
        )->addIndex(
            $installer->getIdxName(
                'digidirect_blog_category_stores',
                ['store_id']
            ),
            ['store_id']
        )->addForeignKey(
            $installer->getFkName('digidirect_blog_category_stores', 'category_id', 'digidirect_blog_category', 'entity_id'),
            'category_id',
            'digidirect_blog_category',
            'entity_id',
            AdapterInterface::FK_ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('digidirect_blog_category_stores', 'store_id', 'store', 'store_id'),
            'store_id',
            'store',
            'store_id',
            AdapterInterface::FK_ACTION_CASCADE
        );

        $installer->getConnection()->createTable($table);
        return $this;
    }

    /**
     * @param SchemaSetupInterface $installer
     * @return $this
     * @throws \Zend_Db_Exception
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function createBlogPost(SchemaSetupInterface $installer)
    {
        $table = $installer->getConnection()->newTable(
            $installer->getTable(PostInterface::DIGIDIRECT_BLOG_POST_TABLE)
        )->addColumn(
            'entity_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Blog Post ID'
        )->addColumn(
            'title',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Title'
        )->addColumn(
            'url_key',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'URL key'
        )->addColumn(
            'content',
            Table::TYPE_TEXT,
            '2M',
            ['nullable' => false],
            'Content'
        )->addColumn(
            'short_content',
            Table::TYPE_TEXT,
            '2M',
            ['nullable' => false],
            'Short Content'
        )->addColumn(
            'image',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Featured Image'
        )->addColumn(
            'image_thumb',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Thumb Image'
        )->addColumn(
            'meta_title',
            Table::TYPE_TEXT,
            '2M',
            ['nullable' => false],
            'Meta Keywords'
        )->addColumn(
            'meta_keywords',
            Table::TYPE_TEXT,
            '2M',
            ['nullable' => false],
            'Meta Keywords'
        )->addColumn(
            'meta_description',
            Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'Meta Description'
        )->addColumn(
            'publish_date',
            Table::TYPE_DATE,
            null,
            [],
            'Publish Date'
        )->addColumn(
            'status',
            Table::TYPE_SMALLINT,
            null,
            [],
            'Active Status'
        )->addColumn(
            'created_at',
            Table::TYPE_TIMESTAMP,
            null,
            [],
            'Creation Time'
        )->addColumn(
            'update_time',
            Table::TYPE_TIMESTAMP,
            null,
            [],
            'Modification Time'
        )->addColumn(
            'views',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Views'
        )->addColumn(
            'comments_count',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => 0],
            'Comments Count'
        )->addColumn(
            'image_sku',
            Table::TYPE_TEXT,
            64,
            ['nullable' => true],
            'Main image sku'
        )->setComment('Blog Table');

        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('digidirect_blog_post_categories')
        )->addColumn(
            'post_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['nullable' => false, 'unsigned' => true, 'primary' => true],
            'Post Id'
        )->addColumn(
            'category_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['nullable' => false, 'unsigned' => true, 'primary' => true],
            'Category Id'
        )->setComment(
            'Posts for stores'
        )->addIndex(
            $installer->getIdxName(
                'digidirect_blog_post_categories',
                ['category_id']
            ),
            ['category_id']
        )->addForeignKey(
            $installer->getFkName(
                'digidirect_blog_post_categories',
                'post_id',
                PostInterface::DIGIDIRECT_BLOG_POST_TABLE,
                'entity_id'
            ),
            'post_id',
            PostInterface::DIGIDIRECT_BLOG_POST_TABLE,
            'entity_id',
            AdapterInterface::FK_ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('digidirect_blog_post_categories', 'category_id', 'digidirect_blog_category', 'entity_id'),
            'category_id',
            'digidirect_blog_category',
            'entity_id',
            AdapterInterface::FK_ACTION_CASCADE
        );

        $installer->getConnection()->createTable($table);
        return $this;
    }

    /**
     * @param SchemaSetupInterface $installer
     * @return $this
     * @throws \Zend_Db_Exception
     */
    protected function createBlogComment(SchemaSetupInterface $installer)
    {
        $table = $installer->getConnection()->newTable(
            $installer->getTable('digidirect_blog_comment')
        )->addColumn(
            'entity_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity ID'
        )->addColumn(
            'post_id',
            Table::TYPE_INTEGER,
            10,
            ['nullable' => false, 'unsigned' => true],
            'Blog Post ID'
        )->addColumn(
            'comment',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Comment'
        )->addColumn(
            'sender_name',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Name'
        )->addColumn(
            'sender_email',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Email'
        )->addColumn(
            'comment_date',
            Table::TYPE_DATETIME,
            null,
            ['nullable' => false],
            'Comment Date'
        )->addColumn(
            'comment_status',
            Table::TYPE_SMALLINT,
            null,
            [],
            'Comment Status'
        )->addIndex(
            $installer->getIdxName('digidirect_blog_comment', ['post_id']),
            ['post_id']
        )->addForeignKey(
            $installer
                ->getFkName(
                    'digidirect_blog_comment',
                    'post_id',
                    PostInterface::DIGIDIRECT_BLOG_POST_TABLE,
                    'entity_id'
                ),
            'post_id',
            $installer->getTable(PostInterface::DIGIDIRECT_BLOG_POST_TABLE),
            'entity_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Comments Table'
        );

        $installer->getConnection()->createTable($table);
        return $this;
    }

    /**
     * @param SchemaSetupInterface $installer
     * @return $this
     * @throws \Zend_Db_Exception
     */
    protected function createRelatedProduct(SchemaSetupInterface $installer)
    {
        $table = $installer->getConnection()->newTable(
            $installer->getTable('digidirect_blog_post_related_products')
        )->addColumn(
            'post_id',
            Table::TYPE_INTEGER,
            10,
            ['nullable' => false, 'unsigned' => true, 'primary' => true],
            'Post ID'
        )->addColumn(
            'product_id',
            Table::TYPE_INTEGER,
            10,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Related Product ID'
        )->addColumn(
            'position',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Position'
        )->addIndex(
            $installer
                ->getIdxName(
                    'digidirect_blog_post_related_products',
                    ['product_id']
                ),
            ['product_id']
        )->addForeignKey(
            $installer
                ->getFkName(
                    'digidirect_blog_post_related_products',
                    'post_id',
                    PostInterface::DIGIDIRECT_BLOG_POST_TABLE,
                    'entity_id'
                ),
            'post_id',
            $installer->getTable(PostInterface::DIGIDIRECT_BLOG_POST_TABLE),
            'entity_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(
                'digidirect_blog_post_related_products',
                'product_id',
                'catalog_product_entity',
                'entity_id'
            ),
            'product_id',
            $installer->getTable('catalog_product_entity'),
            'entity_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Digidirect Blog Post To Product Relation Table'
        );
        $installer->getConnection()->createTable($table);
        return $this;
    }

    /**
     * @param SchemaSetupInterface $installer
     * @return $this
     * @throws \Zend_Db_Exception
     */
    protected function createRelatedPost(SchemaSetupInterface $installer)
    {
        $table = $installer->getConnection()->newTable(
            $installer->getTable('digidirect_blog_post_related_post')
        )->addColumn(
            'post_id',
            Table::TYPE_INTEGER,
            10,
            ['nullable' => false, 'unsigned' => true, 'primary' => true],
            'Post ID'
        )->addColumn(
            'related_id',
            Table::TYPE_INTEGER,
            10,
            ['nullable' => false, 'unsigned' => true, 'primary' => true],
            'Related Post ID'
        )->addColumn(
            'position',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Position'
        )->addIndex(
            $installer
                ->getIdxName(
                    'digidirect_blog_post_related_post',
                    ['related_id']
                ),
            ['related_id']
        )->addForeignKey(
            $installer->getFkName(
                'digidirect_blog_post_related_post',
                'post_id',
                PostInterface::DIGIDIRECT_BLOG_POST_TABLE,
                'entity_id'
            ),
            'post_id',
            $installer->getTable(PostInterface::DIGIDIRECT_BLOG_POST_TABLE),
            'entity_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(
                'digidirect_blog_post_related_post2',
                'related_id',
                PostInterface::DIGIDIRECT_BLOG_POST_TABLE,
                'entity_id'
            ),
            'related_id',
            $installer->getTable(PostInterface::DIGIDIRECT_BLOG_POST_TABLE),
            'entity_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Digidirect Blog Post To Post Relation Table'
        );
        $installer->getConnection()->createTable($table);
        return $this;
    }

    /**
     * @param SchemaSetupInterface $installer
     * @return $this
     * @throws \Zend_Db_Exception
     */
    protected function createTag(SchemaSetupInterface $installer)
    {
        $table = $installer->getConnection()->newTable(
            $installer->getTable('digidirect_blog_tags')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Tag Id'
        )->addColumn(
            'name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '255',
            ['nullable' => false, 'default' => ''],
            'Tag Name'
        )->addColumn(
            'post_count',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'default' => 0],
            'Number posts'
        )->addColumn(
            'website_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'default' => 0],
            'Website Id'
        );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('digidirect_blog_post_tags')
        )->addColumn(
            'post_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['nullable' => false, 'unsigned' => true, 'primary' => true],
            'Post Id'
        )->addColumn(
            'tag_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['nullable' => false, 'unsigned' => true, 'primary' => true],
            'Tag Id'
        )->setComment(
            'Tags for Posts'
        )->addIndex(
            $installer->getIdxName(
                'digidirect_blog_post_tags',
                ['tag_id']
            ),
            ['tag_id']
        )->addForeignKey(
            $installer->getFkName('digidirect_blog_post_tags', 'post_id', PostInterface::DIGIDIRECT_BLOG_POST_TABLE, 'entity_id'),
            'post_id',
            PostInterface::DIGIDIRECT_BLOG_POST_TABLE,
            'entity_id',
            AdapterInterface::FK_ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('digidirect_blog_post_tags', 'tag_id', 'digidirect_blog_tags', 'entity_id'),
            'tag_id',
            'digidirect_blog_tags',
            'entity_id',
            AdapterInterface::FK_ACTION_CASCADE
        );

        $installer->getConnection()->createTable($table);
        return $this;
    }
}
