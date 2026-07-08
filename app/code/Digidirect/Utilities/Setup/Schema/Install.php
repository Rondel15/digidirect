<?php

namespace Digidirect\Utilities\Setup\Schema;

use Digidirect\Utilities\Setup\Helper;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class introduced to get rid of version comparing and copy-pasting install function
 * Just extend this class and write callbacks
 * @since 1.16.3
 */
abstract class Install extends Helper implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->process($setup, $context);
    }
}
