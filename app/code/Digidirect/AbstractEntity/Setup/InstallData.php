<?php
namespace Digidirect\AbstractEntity\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * Setup factory
     *
     * @var AbstractEntitySetupFactory
     */
    protected $abstractEntitySetupFactory;

    /**
     * @param AbstractEntitySetupFactory $abstractEntitySetupFactory
     */
    public function __construct(AbstractEntitySetupFactory $abstractEntitySetupFactory)
    {
        $this->abstractEntitySetupFactory = $abstractEntitySetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var \Digidirect\AbstractEntity\Setup\AbstractEntitySetup $brandSetup */
        $brandSetup = $this->abstractEntitySetupFactory->create(['setup' => $setup]);
        $brandSetup->installEntities();
    }
}
