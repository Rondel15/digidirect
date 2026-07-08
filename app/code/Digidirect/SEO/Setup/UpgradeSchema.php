<?php
namespace Digidirect\SEO\Setup;

use Digidirect\SEO\Model\SitemapExclude;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Upgrade the module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var ModuleContextInterface
     */
    protected $context;

    /**
     * @var SchemaSetupInterface
     */
    protected $setup;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->context = $context;
        $this->setup = $setup;
        $this->connection = $setup->getConnection();

        $this->setup->startSetup();

        if ($this->compareVersions('1.0.1')) {
            $this->createSeoSitemapExcludeTable();
        }

        if ($this->compareVersions('1.0.2')) {
            $this->updateSeoSitemapExcludeTable();
        }

        $this->setup->endSetup();
    }

    /**
     * Create sitemap excluded items table
     *
     * @return void
     */
    protected function createSeoSitemapExcludeTable()
    {
        $sitemapExcludeTable = \Digidirect\SEO\Model\ResourceModel\SitemapExclude::TABLE_NAME;

        if (!$this->isTableExists($sitemapExcludeTable)) {
            $table = $this->connection->newTable(
                $this->setup->getTable($sitemapExcludeTable)
            )->addColumn(
                SitemapExclude::EXCLUDE_ID,
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true, 'identity' => true],
                'ID'
            )->addColumn(
                SitemapExclude::ITEM_TYPE,
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false,
                ],
                'Excluded item type'
            )->addColumn(
                SitemapExclude::ITEM_ID,
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false,
                ],
                'Excluded item ID'
            )->addColumn(
                SitemapExclude::STORE_ID,
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false,
                ],
                'Excluded store ID'
            )->addColumn(
                SitemapExclude::STATUS,
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => false,
                ],
                'Exclude status'
            )->setComment('Sitemap excluded items table');
            $this->connection->createTable($table);
        }
    }

    /**
     * Create sitemap excluded items table
     *
     * @return void
     */
    protected function updateSeoSitemapExcludeTable()
    {
        $sitemapExcludeTable = \Digidirect\SEO\Model\ResourceModel\SitemapExclude::TABLE_NAME;
        $setup = $this->setup;

        if ($this->isTableExists($sitemapExcludeTable)) {

            $setup->getConnection()->addColumn(
                $setup->getTable($sitemapExcludeTable),
                SitemapExclude::STORE_ID,
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => false,
                    'comment' => 'Sitemap excluded items table'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable($sitemapExcludeTable),
                SitemapExclude::STATUS,
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'nullable' => false,
                    'comment' => 'Excluded store ID'
                ]
            );
        }
    }

    /**
     * Compare versions
     *
     * @param string $new
     * @return bool
     */
    protected function compareVersions($new)
    {
        return version_compare($this->context->getVersion(), $new, '<');
    }

    /**
     * Check if table is exists
     *
     * @param string $tableName
     * @return bool
     */
    protected function isTableExists($tableName)
    {
        return $this->connection->isTableExists($tableName);
    }
}
