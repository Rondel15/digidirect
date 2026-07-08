<?php

namespace Digi\Qantas\Setup;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{

	public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup,
                \Magento\Framework\Setup\ModuleContextInterface $context)
	{
		$installer = $setup;
		$installer->startSetup();
		if (!$installer->tableExists('qantas_qff_member')) {
			$table = $installer->getConnection()->newTable(
				$installer->getTable('qantas_qff_member')
			)
				->addColumn(
					'qff_number',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					16,
					[
						'identity' => false,
						'primary'  => true,
						'unsigned' => true,
					],
					'Qantas Frequent Flyer Number'
				)
                                ->addColumn(
					'last_name',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					[],
					'Last Name'
				)
				->addColumn(
					'first_initial',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					[],
					'First Name Initial '
				)
                                ->addColumn(
					'title',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					[],
					'Title '
				)                            
                                 ->addColumn(
					'transaction_date',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					['nullable => false'],
					'Transaction Date'
				)
                                 ->addColumn(
					'product_description',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					[],
					'Product Description '
				)
                                 ->addColumn(
					'partner_reference',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					[],
					'Partner Reference '
				)
                                 ->addColumn(
					'payment',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					[],
					'Payment '
				)
                                ->addColumn(
					'amount_in_cents',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					[],
					'Amount in Cents'
				)
                                 ->addColumn(
					'base_points_earned',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					[],
					'Base Points Earned'
				)
                                ->addColumn(
					'bonus_points_earned',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					[],
					'Bonus Points Earned'
				)
                                ->addColumn(
					'total_points_earned',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					[],
					'Total Points Earned'
				)
                                ->addColumn(
					'record_number',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					[],
					'Record Number'
				)
                                ->addColumn(
					'points',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					[],
					'Points'
				)
				->addColumn(
					'created_at',
					\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
					null,
					['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
					'Created At'
				)->addColumn(
					'updated_at',
					\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
					null,
					['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
					'Updated At')
				->setComment('Qantas Members Table');
			$installer->getConnection()->createTable($table);

			$installer->endSetup();
		}
		
	}
}