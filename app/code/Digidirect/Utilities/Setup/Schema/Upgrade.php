<?php

namespace Digidirect\Utilities\Setup\Schema;

use Digidirect\Utilities\Setup\Helper;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class introduced to get rid of version comparing and copy-pasting install function
 * Just extend this class and write callbacks
 * @since 1.16.3
 */
abstract class Upgrade extends Helper implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->process($setup, $context);
    }
}
