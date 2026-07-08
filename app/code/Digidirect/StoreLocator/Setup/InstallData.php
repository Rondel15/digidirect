<?php
namespace Digidirect\StoreLocator\Setup;

use Digidirect\StoreLocator\Setup\StoreLocatorEntitySetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class InstallData
 * @package Digidirect\StoreLocator\Setup
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var \Digidirect\StoreLocator\Setup\StoreLocatorEntitySetupFactory
     */
    protected $_storeLocatorEntitySetupFactory;

    /**
     * InstallData constructor.
     * @param \Digidirect\StoreLocator\Setup\StoreLocatorEntitySetupFactory $storeLocatorEntitySetupFactory
     */
    public function __construct(StoreLocatorEntitySetupFactory $storeLocatorEntitySetupFactory)
    {
        $this->_storeLocatorEntitySetupFactory = $storeLocatorEntitySetupFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $entitySetup = $this->_storeLocatorEntitySetupFactory->create(['setup' => $setup]);
        $entitySetup->installEntities();
    }
}