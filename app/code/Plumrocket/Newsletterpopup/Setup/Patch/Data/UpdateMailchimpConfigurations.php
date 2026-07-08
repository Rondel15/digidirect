<?php
/**
 * @package     Plumrocket_NewsletterPopup
 * @copyright   Copyright (c) 2022 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Setup\Patch\Data;

use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\State;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Plumrocket\Newsletterpopup\Helper\Data;

/**
 * @since 4.3.0
 */
class UpdateMailchimpConfigurations implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var \Magento\Config\Model\Config
     */
    private $config;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory
     */
    private $configDataCollectionFactory;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\MailchimpListFactory
     */
    private $mailchimpListFactory;

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     * @param \Magento\Config\Model\ConfigFactory $configFactory
     * @param \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $configDataCollectionFactory
     * @param \Plumrocket\Newsletterpopup\Model\MailchimpListFactory $mailchimpListFactory
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Config\Model\ConfigFactory $configFactory,
        \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $configDataCollectionFactory,
        \Plumrocket\Newsletterpopup\Model\MailchimpListFactory $mailchimpListFactory,
        State $state
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->configDataCollectionFactory = $configDataCollectionFactory;
        $this->mailchimpListFactory = $mailchimpListFactory;

        // after serAreaCode
        $this->config = $configFactory->create();
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $connection = $this->moduleDataSetup->getConnection();

        $this->config->setDataByPath(Data::SECTION_ID . '/mailchimp/fields_tag', json_encode([]));
        $this->config->save();

        /** @var \Magento\Config\Model\ResourceModel\Config\Data\Collection $configDataCollection */
        $configDataCollection = $this->configDataCollectionFactory->create();
        $configDataCollection->addPathFilter('prnewsletterpopup/mailchimp');

        foreach ($configDataCollection as $item) {
            $path = $item->getData('path');
            $newPath = str_replace('/mailchimp/', '/integration/mailchimp/', $path);
            $item->setData('path', $newPath);
        }

        $configDataCollection->save();

        /**
         * Update all records and set mailchimp value for integration_id column
         */
        $tableName = $this->moduleDataSetup->getTable('plumrocket_newsletterpopup_mailchimp_list');
        if ($connection->isTableExists($tableName)) {
            $connection->update(
                $tableName,
                [
                    'integration_id' => 'mailchimp',
                ],
                'integration_id IS NULL'
            );
        }

        /**
         * Update all templates and replace {{mailchimp_fields}} to {{contact_lists}}
         */
        $tableName = $this->moduleDataSetup->getTable('plumrocket_newsletterpopup_templates');

        if ($connection->isTableExists($tableName)) {
            $connection->update(
                $tableName,
                [
                    'code' => new \Zend_Db_Expr('REPLACE(`code`, \'{{mailchimp_fields}}\', \'{{contact_lists}}\')'),
                ]
            );
        }

        /**
         * Enable mailchimp integration if the lists exist
         */
        $mailchimpLists = $this->mailchimpListFactory->create()
            ->getCollection()
            ->addFieldToSelect('popup_id')
            ->addFieldToFilter('integration_id', 'mailchimp')
            ->addFieldToFilter('enable', 1);

        $mailchimpLists->getSelect()->group('popup_id');

        $popupIds = $mailchimpLists->getColumnValues('popup_id');

        if ($popupIds) {
            $connection->update(
                $this->moduleDataSetup->getTable('plumrocket_newsletterpopup_popups'),
                [
                    'integration_enable' => '{"mailchimp":"1"}'
                ],
                'integration_enable IS NULL AND entity_id IN (' . implode(',', $popupIds) . ')'
            );
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion(): string
    {
        return '3.3.0';
    }
}
