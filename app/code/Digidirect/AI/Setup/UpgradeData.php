<?php

namespace Digidirect\AI\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var ModuleContextInterface
     */
    protected $context;

    /**
     * @var ModuleDataSetupInterface
     */
    protected $setup;

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->setup = $setup;
        $this->context = $context;
        $this->setup->startSetup();
        $this->processUpgrade();
        $this->setup->endSetup();
    }

    /**
     * To avoid writing version_compare each time
     * use array with callbacks and versions
     * @return void
     */
    protected function processUpgrade()
    {
        foreach ($this->getCallbacks() as $version => $callbackString) {
            if ($this->compareVersion($version)) {
                $this->$callbackString();
            }
        }
    }

    /**
     * @return array
     */
    protected function getCallbacks()
    {
        return [
            '2.1.10' => 'setQueueFailEmails',
        ];
    }

    /**
     * @param string $version
     * @return bool
     */
    protected function compareVersion(string $version)
    {
        return $this->context->getVersion() && (version_compare($this->context->getVersion(), $version) < 0);
    }

    /**
     * @return \Magento\Framework\DB\Adapter\Pdo\Mysql
     */
    protected function getConnection()
    {
        return $this->setup->getConnection();
    }

    /**
     * @param string $path
     * @param string $scope
     * @param int $scopeId
     * @return string
     */
    protected function getConfigValue(
        $path,
        $scope = \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT,
        $scopeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID
    ) {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->setup->getTable('core_config_data'), 'value')
            ->where('path = ?', $path)
            ->where('scope = ?', $scope)
            ->where('scope_id = ?', $scopeId);

        return $connection->fetchOne($select);
    }

    /**
     * @return $this
     */
    protected function setQueueFailEmails()
    {
        $configValue = $this->getConfigValue(\Digidirect\AI\Helper\Mail::XML_PATH_FAIL_RUN_EMAILS);
        if ($configValue) {
            $connection = $this->getConnection();
            $connection->insertOnDuplicate(
                $this->setup->getTable('core_config_data'),
                [
                    'scope' => \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT,
                    'scope_id' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    'path' => \Digidirect\AI\Helper\Mail::XML_PATH_QUEUE_FAIL_EMAILS,
                    'value' => $configValue,
                ],
                ['scope_id'] //we do not want to override existing value
            );
        }
        return $this;
    }
}
