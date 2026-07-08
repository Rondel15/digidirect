<?php
namespace Digidirect\Store\Setup;

use Digidirect\Store\Setup\StoreEntitySetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class InstallData
 * @package Digidirect\Store\Setup
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var \Digidirect\Store\Setup\StoreEntitySetupFactory
     */
    protected $_storeEntitySetupFactory;

    /**
     * InstallData constructor.
     * @param \Digidirect\Store\Setup\StoreEntitySetupFactory $storeEntitySetupFactory
     */
    public function __construct(StoreEntitySetupFactory $storeEntitySetupFactory)
    {
        $this->_storeEntitySetupFactory = $storeEntitySetupFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $entitySetup = $this->_storeEntitySetupFactory->create(['setup' => $setup]);
        $entitySetup->installEntities();
    }
}