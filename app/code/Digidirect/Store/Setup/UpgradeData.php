<?php

namespace Digidirect\Store\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Eav\Setup\EavSetupFactory;

/**
 * Class UpgradeData
 * @package Digidirect\Event\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var StoreEntitySetupFactory
     */
    private $storeEntitySetupFactory;

    /**
     * @var ModuleContextInterface
     */
    protected $context;

    /**
     * UpgradeData constructor.
     *
     * @param StoreEntitySetupFactory $storeEntitySetupFactory
     */
    public function __construct(
        StoreEntitySetupFactory $storeEntitySetupFactory
    ) {
        $this->storeEntitySetupFactory = $storeEntitySetupFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $entitySetup = $this->storeEntitySetupFactory->create(['setup' => $setup]);
        $entitySetup->upgradeData($context);
    }
}