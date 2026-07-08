<?php

// @codingStandardsIgnoreFile

namespace Digidirect\Utilities\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\SetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 *
 * Reusable class for install/upgrade scripts
 * Feel free to add useful functions
 * @since 1.16.3
 */
abstract class Helper
{
    /**
     * @var ModuleContextInterface $context
     */
    protected $context;

    /**
     * @var SchemaSetupInterface
     */
    protected $setup;

    /**
     * [
     *     '1.0.1' => 'functionName'
     * ]
     * @return array
     */
    abstract protected function getCallbacks(): array;

    /**
     * @param SetupInterface $setup
     * @param ModuleContextInterface $moduleContext
     */
    protected function process(SetupInterface $setup, ModuleContextInterface $moduleContext)
    {
        $this->setup = $setup;
        $this->context = $moduleContext;
        $this->setup->startSetup();
        foreach ($this->getCallbacks() as $versionNumber => $callback) {

            if ($this->versionCompare($versionNumber)) {
                if (is_callable($callback)) {
                    call_user_func($callback);
                }

                if (is_string($callback)) {
                    $this->$callback();
                }
            }
        }
        $this->setup->endSetup();
    }

    /**
     * @param string $version
     * @return bool
     */
    protected function versionCompare(string $version): bool
    {
        return version_compare($this->getContext()->getVersion(), $version, '<');
    }

    /**
     * @return ModuleContextInterface
     */
    protected function getContext(): ModuleContextInterface
    {
        return $this->context;
    }

    /**
     * @return SchemaSetupInterface
     */
    protected function getSetup(): SetupInterface
    {
        return $this->setup;
    }

    /**
     * @param string $tableName
     * @return string
     */
    protected function getTable(string $tableName): string
    {
        return $this->getSetup()->getTable($tableName);
    }

    /**
     * @return AdapterInterface
     */
    protected function getConnection(): AdapterInterface
    {
        return $this->getSetup()->getConnection();
    }
}
