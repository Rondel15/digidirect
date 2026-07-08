<?php

namespace Digidirect\Blog\Setup;

use Digidirect\Blog\Api\Data\CategoryContentInterface;
use Digidirect\Blog\Model\ResourceModel\Category;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Digidirect\Blog\Api\Data\PostContentInterface;
use Digidirect\Blog\Api\Data\PostInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Digidirect\Blog\Api\Data\TagInterface;

/**
 * Class UpgradeSchema
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var SchemaSetupInterface
     */
    protected $setup;

    /**
     * @var ModuleContextInterface
     */
    protected $context;

    /**
     * {@inheritdoc}
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->context = $context;
        $this->setup = $setup;

        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->addTrendingImage($setup);
        }
        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $this->changeCommentSize($setup);
        }
        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $this->_createPostInformationTable($setup);
            $this->_upgradeBlogTableForStoreView($setup);
        }
        if (version_compare($context->getVersion(), '1.0.4', '<')) {
            $this->createCategoryInformationTable();
        }
        if (version_compare($context->getVersion(), '1.0.5', '<')) {
            $this->deleteColumns();
        }
        if (version_compare($context->getVersion(), '1.0.6', '<')) {
            $this->modifyCreatedAtFieldInPosts($setup);
            $this->addDateFieldsToCategoriesAndPosts($setup);
        }
        if (version_compare($context->getVersion(), '1.0.7', '<')) {
            $this->addColumnToPostInformationForStoreView($setup);
        }
        if (version_compare($context->getVersion(), '1.0.8', '<')) {
            $this->updateBlogPostTagTable($setup);
        }
        if (version_compare($context->getVersion(), '1.0.9', '<')) {
            $this->updatePostTagsTableForStoreView($setup);
        }

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function modifyCreatedAtFieldInPosts(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->modifyColumn(
            $setup->getTable(PostInterface::DIGIDIRECT_BLOG_POST_TABLE),
            'created_at',
            $this->getDateFieldparams('Created At')
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function addDateFieldsToCategoriesAndPosts(SchemaSetupInterface $setup)
    {
        $createdAt = 'created_at';
        $createdAtComment = 'Created At';
        $updatedAt = 'updated_at';
        $updatedAtComment = 'Updated At';
        $this->addDateField($setup, Category::DIGIDIRECT_BLOG_CATEGORY_INFORMATION_TABLE, $createdAt, $createdAtComment);
        $this->addDateField($setup, Category::DIGIDIRECT_BLOG_CATEGORY_INFORMATION_TABLE, $updatedAt, $updatedAtComment);
        $this->addDateField($setup, PostInterface::DIGIDIRECT_BLOG_POST_TABLE, $updatedAt, $updatedAtComment);
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param string $table
     * @param string $field
     * @param string $comment
     * @return void
     */
    protected function addDateField(SchemaSetupInterface $setup, $table, $field, $comment)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable($table),
            $field,
            $this->getDateFieldparams($comment)
        );
    }

    /**
     * @param string $comment
     * @return array
     */
    protected function getDateFieldparams($comment)
    {
        return [
            'type' => Table::TYPE_TIMESTAMP,
            'nullable' => false,
            'default' => Table::TIMESTAMP_INIT,
            'comment' => $comment,
        ];
    }

    /**
     * @param string $newVersion
     * @return mixed
     */
    protected function versionCompare($newVersion)
    {
        return version_compare($this->context->getVersion(), $newVersion, '<');
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function addTrendingImage(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable(PostInterface::DIGIDIRECT_BLOG_POST_TABLE),
            'image_trending',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'comment' => 'Trending Image',
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function changeCommentSize(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->modifyColumn(
            $setup->getTable('digidirect_blog_comment'),
            'comment',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Comment',
            ]
        );
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function getConnection(): \Magento\Framework\DB\Adapter\AdapterInterface
    {
        return $this->setup->getConnection();
    }

    /**
     * @param string $tableName
     * @return string
     */
    protected function getTable(string $tableName): string
    {
        return $this->setup->getTable($tableName);
    }

    /**
     * @return UpgradeSchema
     * @throws \Zend_Db_Exception
     */
    protected function createCategoryInformationTable(): self
    {
        $tableCat = $this->getConnection()->newTable(
            $this->getTable(Category::DIGIDIRECT_BLOG_CATEGORY_INFORMATION_TABLE)
        )->addColumn(
            CategoryContentInterface::CATEGORY_ID,
            Table::TYPE_INTEGER,
            10,
            ['unsigned' => true, 'nullable' => false],
            'Category ID'
        )->addColumn(
            'name',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Category Name'
        )->addColumn(
            'url_key',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Category URL Key'
        )->addColumn(
            'status',
            Table::TYPE_SMALLINT,
            null,
            [],
            'Category Status'
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
            CategoryContentInterface::STORE_ID,
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            5,
            ['nullable' => false, 'unsigned' => true],
            'Store Id'
        )->addIndex(
            $this->setup->getIdxName(
                Category::DIGIDIRECT_BLOG_CATEGORY_INFORMATION_TABLE,
                [CategoryContentInterface::STORE_ID, CategoryContentInterface::CATEGORY_ID],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            [CategoryContentInterface::STORE_ID, CategoryContentInterface::CATEGORY_ID],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addForeignKey(
            $this->getFkName(
                Category::DIGIDIRECT_BLOG_CATEGORY_INFORMATION_TABLE,
                CategoryContentInterface::CATEGORY_ID,
                Category::DIGIDIRECT_BLOG_CATEGORY_TABLE,
                'entity_id'
            ),
            CategoryContentInterface::CATEGORY_ID,
            Category::DIGIDIRECT_BLOG_CATEGORY_TABLE,
            'entity_id',
            AdapterInterface::FK_ACTION_CASCADE
        )->addForeignKey(
            $this->getFkName(
                Category::DIGIDIRECT_BLOG_CATEGORY_INFORMATION_TABLE,
                CategoryContentInterface::STORE_ID,
                'store',
                'store_id'
            ),
            CategoryContentInterface::STORE_ID,
            'store',
            'store_id',
            AdapterInterface::FK_ACTION_CASCADE
        )->setComment(
            'Category Information Table'
        );

        $this->getConnection()->createTable($tableCat);
        return $this;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return $this
     * @throws \Zend_Db_Exception
     */
    protected function _createPostInformationTable(SchemaSetupInterface $setup)
    {
        $newTable = $setup->getConnection()->newTable(
            $setup->getTable(PostContentInterface::DIGIDIRECT_BLOG_POST_INFORMATION_TABLE)
        )->addColumn(
            PostContentInterface::DIGIDIRECT_BLOG_POST_INFORMATION_TABLE_ID,
            Table::TYPE_INTEGER,
            10,
            ['unsigned' => true, 'nullable' => false],
            'Post ID'
        )->addColumn(
            PostContentInterface::DIGIDIRECT_BLOG_POST_INFORMATION_TABLE_TITLE,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Title'
        )->addColumn(
            PostContentInterface::DIGIDIRECT_BLOG_POST_INFORMATION_TABLE_CONTENT,
            Table::TYPE_TEXT,
            '2M',
            ['nullable' => false],
            'Content'
        )->addColumn(
            PostContentInterface::DIGIDIRECT_BLOG_POST_INFORMATION_TABLE_SHORT_CONTENT,
            Table::TYPE_TEXT,
            '2M',
            ['nullable' => false],
            'Short Content'
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
            PostContentInterface::STORE_ID,
            Table::TYPE_SMALLINT,
            5,
            ['nullable' => false, 'unsigned' => true],
            'Store Id'
        )->addIndex(
            $setup->getIdxName(
                PostContentInterface::DIGIDIRECT_BLOG_POST_INFORMATION_TABLE,
                [PostContentInterface::STORE_ID, PostContentInterface::DIGIDIRECT_BLOG_POST_INFORMATION_TABLE_ID],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            [PostContentInterface::STORE_ID, PostContentInterface::DIGIDIRECT_BLOG_POST_INFORMATION_TABLE_ID],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addForeignKey(
            $setup->getFkName(
                PostContentInterface::DIGIDIRECT_BLOG_POST_INFORMATION_TABLE,
                PostContentInterface::DIGIDIRECT_BLOG_POST_INFORMATION_TABLE_ID,
                PostInterface::DIGIDIRECT_BLOG_POST_TABLE,
                'entity_id'
            ),
            PostContentInterface::DIGIDIRECT_BLOG_POST_INFORMATION_TABLE_ID,
            PostInterface::DIGIDIRECT_BLOG_POST_TABLE,
            'entity_id',
            AdapterInterface::FK_ACTION_CASCADE
        )->addForeignKey(
            $setup->getFkName(
                PostContentInterface::DIGIDIRECT_BLOG_POST_INFORMATION_TABLE,
                PostContentInterface::STORE_ID,
                'store',
                'store_id'
            ),
            PostContentInterface::STORE_ID,
            'store',
            'store_id',
            AdapterInterface::FK_ACTION_CASCADE
        )->setComment(
            'Post Information Table'
        );

        $setup->getConnection()->createTable($newTable);

        return $this;
    }

    /**
     * @param string $priTableName
     * @param string $priColumnName
     * @param string $refTableName
     * @param string $refColumnName
     * @return string
     */
    protected function getFkName($priTableName, $priColumnName, $refTableName, $refColumnName)
    {
        return $this->setup->getFkName($priTableName, $priColumnName, $refTableName, $refColumnName);
    }

    /**
     * @return UpgradeSchema
     */
    protected function deleteColumns(): self
    {
        $columns = [
            'name',
            'status',
            'order',
            'meta_title',
            'meta_keywords',
            'meta_description',
            'url_key',
        ];
        foreach ($columns as $column) {
            $this->getConnection()->dropColumn($this->getTable(Category::DIGIDIRECT_BLOG_CATEGORY_TABLE), $column);
        }

        return $this;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    protected function _upgradeBlogTableForStoreView($setup)
    {
        $columns = [
            'title',
            'content',
            'short_content',
            'meta_title',
            'meta_keywords',
            'meta_description',
        ];
        foreach ($columns as $column) {
            $setup->getConnection()->dropColumn($setup->getTable(PostInterface::DIGIDIRECT_BLOG_POST_TABLE), $column);
        }

        return $this;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    protected function addColumnToPostInformationForStoreView($setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable(PostContentInterface::DIGIDIRECT_BLOG_POST_INFORMATION_TABLE),
            'url_key',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'URL key'
            ]
        );
        $setup->getConnection()->addColumn(
            $setup->getTable(PostContentInterface::DIGIDIRECT_BLOG_POST_INFORMATION_TABLE),
            'status',
            [
                'type' => Table::TYPE_SMALLINT,
                'comment' => 'Status'
            ]
        );

        $this->movePostColumnsValueToInformationTable($setup);
        return $this;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    protected function movePostColumnsValueToInformationTable($setup)
    {
        $columns = [
            'url_key',
            'status'
        ];
        $adapter = $setup->getConnection();
        $tableDescription = $adapter->describeTable(PostInterface::DIGIDIRECT_BLOG_POST_TABLE);
        $fieldsToSelect = array_keys($tableDescription);
        $fieldsToSelect = array_intersect($columns, $fieldsToSelect);
        if (!empty($fieldsToSelect)) {
            $fieldsToSelect[] = PostInterface::FIELD_ID;
            $select = $adapter->select()
                ->from(PostInterface::DIGIDIRECT_BLOG_POST_TABLE)
                ->reset(\Zend_Db_Select::COLUMNS)
                ->columns($fieldsToSelect);
            $result = $adapter->fetchAll($select);
            $oldData = [];
            foreach ($result as $row) {
                $key = $row[PostInterface::FIELD_ID];
                unset($row[PostInterface::FIELD_ID]);
                $oldData[$key] = $row;
            }

            $select = $adapter->select()
                ->from(PostContentInterface::DIGIDIRECT_BLOG_POST_INFORMATION_TABLE)
                ->reset(\Zend_Db_Select::COLUMNS)
                ->columns([
                    PostContentInterface::DIGIDIRECT_BLOG_POST_INFORMATION_TABLE_ID,
                    PostContentInterface::STORE_ID,
                    PostContentInterface::DIGIDIRECT_BLOG_POST_INFORMATION_TABLE_URL_KEY,
                    PostInterface::FIELD_STATUS
                ]);
            $result = $adapter->fetchAll($select);
            foreach ($result as $key => &$row) {
                $newData = !empty($oldData[$row[PostContentInterface::DIGIDIRECT_BLOG_POST_INFORMATION_TABLE_ID]])
                    ? $oldData[$row[PostContentInterface::DIGIDIRECT_BLOG_POST_INFORMATION_TABLE_ID]]
                    : [];
                if (empty($newData)) {
                    unset($result[$key]);
                }
                $row = array_merge($row, $newData);
            }
            $adapter->insertOnDuplicate(
                PostContentInterface::DIGIDIRECT_BLOG_POST_INFORMATION_TABLE,
                $result,
                $columns
            );

            foreach ($columns as $column) {
                $setup->getConnection()->dropColumn(
                    $setup->getTable(PostInterface::DIGIDIRECT_BLOG_POST_TABLE),
                    $column
                );
            }
        }

        return $this;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    protected function updateBlogPostTagTable($setup)
    {
        $setup->getConnection()->addIndex(
            TagInterface::DIGIDIRECT_BLOG_POST_TAG_TABLE,
            $setup->getIdxName(
                TagInterface::DIGIDIRECT_BLOG_POST_TAG_TABLE,
                ['post_id']
            ),
            ['post_id']
        );
        return $this;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    protected function updatePostTagsTableForStoreView($setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable(TagInterface::DIGIDIRECT_BLOG_POST_TAG_TABLE),
            'store_id',
            [
                'type' => Table::TYPE_SMALLINT,
                'length' => 5,
                'nullable' => false,
                'unsigned' => true,
                'comment' => 'Store Id'
            ]
        );

        $setup->getConnection()->dropIndex(
            TagInterface::DIGIDIRECT_BLOG_POST_TAG_TABLE,
            'primary'
        );

        $setup->getConnection()->addIndex(
            TagInterface::DIGIDIRECT_BLOG_POST_TAG_TABLE,
            $setup->getIdxName(
                TagInterface::DIGIDIRECT_BLOG_POST_TAG_TABLE,
                ['post_id', 'tag_id', 'store_id']
            ),
            ['post_id', 'tag_id', 'store_id'],
            AdapterInterface::INDEX_TYPE_PRIMARY
        );

        $setup->getConnection()->addForeignKey(
            $setup->getFkName(
                TagInterface::DIGIDIRECT_BLOG_POST_TAG_TABLE,
                'store_id',
                'store',
                'store_id'
            ),
            TagInterface::DIGIDIRECT_BLOG_POST_TAG_TABLE,
            'store_id',
            'store',
            'store_id'
        );
        return $this;
    }
}
