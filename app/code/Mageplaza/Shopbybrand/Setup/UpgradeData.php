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
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Shopbybrand\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class UpgradeData
 * @package Mageplaza\Shopbybrand\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var DateTime
     */
    public $date;

    /**
     * UpgradeData constructor.
     *
     * @param DateTime $date
     */
    public function __construct(
        DateTime $date
    ) {
        $this->date = $date;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '2.4.0', '<')) {
            /** Add create at old comment */
            $sampleTemplates = [
                [
                    'id'          => '10',
                    'code'        => 'mageplaza_reports_brand_aggregated',
                    'name'        => 'Brands',
                    'description' => 'Brands Report',
                ]
            ];

            $setup->getConnection()->insertOnDuplicate(
                $setup->getTable('mageplaza_brand_reports_reindex'),
                $sampleTemplates,
                ['code', 'name', 'description']
            );
        }

        $installer->endSetup();
    }
}
