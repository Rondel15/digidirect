<?php

namespace Digidirect\StoreLocator\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Digidirect\StoreLocator\Setup\StoreLocatorEntitySetupFactory;

/**
 * Class UpgradeData
 * @package Digidirect\StoreLocator\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var StoreLocatorEntitySetupFactory
     */
    private $storeLocatorEntitySetupFactory;

    /**
     * @var ModuleContextInterface
     */
    protected $context;

    /**
     * UpgradeData constructor.
     *
     * @param StoreLocatorEntitySetupFactory $storeLocatorEntitySetupFactory
     */
    public function __construct(
        StoreLocatorEntitySetupFactory $storeLocatorEntitySetupFactory
    ) {
        $this->storeLocatorEntitySetupFactory = $storeLocatorEntitySetupFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $entitySetup = $this->storeLocatorEntitySetupFactory->create(['setup' => $setup]);
        $entitySetup->upgradeData($context);
    }
}