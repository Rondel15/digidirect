<?php

namespace Digidirect\Localization\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Class UpgradeData
 */
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
        $this->context = $context;
        $this->setup = $setup;
        $this->setup->startSetup();
        $this->process();
        $this->setup->endSetup();
    }

    /**
     * @param string $newVersion
     * @return bool
     */
    protected function compareVersion($newVersion)
    {
        return version_compare($this->context->getVersion(), $newVersion) < 0;
    }

    /**
     * @return array
     */
    protected function getProcesses()
    {
        return [
            '1.0.1' => 'checkAndRemoveDoubledStates',
            '1.0.2' => 'addPuertoRicoCountry',
        ];
    }

    /**
     * @return void
     */
    protected function process()
    {
        foreach ($this->getProcesses() as $version => $process) {
            if ($this->compareVersion($version)) {
                $this->$process();
            }
        }
    }

    /**
     * As an example from TAF, when another extension adds australian states we should remove them
     *
     * @return void
     */
    protected function checkAndRemoveDoubledStates()
    {
        $select = $this->setup->getConnection()->select()
            ->from($this->setup->getTable('directory_country_region'), ['region_id'])
            ->where('country_id = ?', 'AU')
            ->group('code')
            ->having(new \Zend_Db_Expr('COUNT(code) > 1'));

        $result = $this->setup->getConnection()->fetchCol($select);
        if (!empty($result)) {
            $this->setup->getConnection()->delete(
                $this->setup->getTable('directory_country_region'),
                $this->setup->getConnection()->quoteInto('region_id IN (?)', $result)
            );
        }
    }

    protected function addPuertoRicoCountry()
    {
        $data = [
            'country_id' => 'PR',
            'iso2_code' => 'PR',
            'iso3_code' => 'PRI'
        ];

        $this->setup->getConnection()->insert(
            $this->setup->getTable('directory_country'),
            $data
        );
    }
}
