<?php

namespace Digidirect\Blog\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Config\Model\ResourceModel\Config;

class UpgradeData implements UpgradeDataInterface
{

    /**
     * @var Config
     */
    protected $config;

    /**
     * UpgradeSchema constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.10', '<')) {
            $this->changeSettingsPath($setup);
        }

        $setup->endSetup();
    }


    /**
     * @param ModuleDataSetupInterface $setup
     * @return $this
     */
    protected function changeSettingsPath($setup)
    {
        $connection = $setup->getConnection();
        $select = $connection->select()->from($this->config->getMainTable())->where('path LIKE (?)', 'digidirect_blog%');
        $result = $connection->fetchAll($select);

        foreach ($result as $item) {
            $path = explode('/', $item['path']);
            if (count($path) == 4) {
                $newPath = implode('/', $path);
                $this->config->saveConfig($newPath, $item['value'], $item['scope'], $item['scope_id']);
            }
        }

        return $this;
    }
}
