<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Milestone
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
declare(strict_types=1);

namespace Mageplaza\Webhook\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Mageplaza\Webhook\Model\CronScheduleFactory;
use Mageplaza\Webhook\Model\HistoryFactory;
use Mageplaza\Webhook\Model\HookFactory;

/**
* Patch is mechanism, that allows to do atomic upgrade data changes
*/
class UpdateHookType implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @var HookFactory
     */
    protected $hookFactory;

    /**
     * @var CronScheduleFactory
     */
    protected $cronScheduleFactory;

    /**
     * @var HistoryFactory
     */
    protected $historyFactory;

    /**
     * UpdateHookType constructor.
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param HookFactory $hookFactory
     * @param CronScheduleFactory $cronScheduleFactory
     * @param HistoryFactory $historyFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        HookFactory $hookFactory,
        CronScheduleFactory $cronScheduleFactory,
        HistoryFactory $historyFactory
    ) {
        $this->moduleDataSetup     = $moduleDataSetup;
        $this->hookFactory         = $hookFactory;
        $this->cronScheduleFactory = $cronScheduleFactory;
        $this->historyFactory      = $historyFactory;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        /**
         * Update hook type of mageplaza_webhook_hook table
         */
        $hookCollections = $this->hookFactory->create()->getCollection()
            ->addFieldToFilter('hook_type', ['eq' => 'new_order']);
        foreach ($hookCollections as $hook) {
            $hook->setHookType('order');
        }

        $hookCollections->save();

        /**
         * Update hook type of mageplaza_webhook_cron_schedule table
         */
        $cronScheduleCollections = $this->cronScheduleFactory->create()->getCollection()
            ->addFieldToFilter('hook_type', ['eq' => 'new_order']);
        foreach ($cronScheduleCollections as $cronSchedule) {
            $cronSchedule->setHookType('order');
        }

        $cronScheduleCollections->save();

        /**
         * Update hook type of mageplaza_webhook_history table
         */
        $historyCollections = $this->historyFactory->create()->getCollection()
            ->addFieldToFilter('hook_type', ['eq' => 'new_order']);
        foreach ($historyCollections as $history) {
            $history->setHookType('order');
        }

        $historyCollections->save();
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.1';
    }
}
