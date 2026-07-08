<?php

namespace Digidirect\Utilities\Setup\Data;

use Digidirect\Utilities\Setup\Helper;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Class introduced to get rid of version comparing and copy-pasting upgrade function
 * Just extend this class and write callbacks
 * @since 1.16.3
 */
abstract class Upgrade extends Helper implements UpgradeDataInterface
{
    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->process($setup, $context);
    }
}
