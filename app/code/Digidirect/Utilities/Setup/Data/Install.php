<?php

namespace Digidirect\Utilities\Setup\Data;

use Digidirect\Utilities\Setup\Helper;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class introduced to get rid of version comparing and copy-pasting install function
 * Just extend this class and write callbacks
 * @since 1.16.3
 */
abstract class Install extends Helper implements InstallDataInterface
{
    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->process($setup, $context);
    }
}
