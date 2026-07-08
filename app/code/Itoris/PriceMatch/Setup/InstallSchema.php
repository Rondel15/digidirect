<?php
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_M2_ITORIS_PRICE_MATCH
 * @copyright  Copyright (c) 2018 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

namespace Itoris\PriceMatch\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Itoris\PriceMatch\Helper\Data as HelperData;

class InstallSchema implements InstallSchemaInterface
{

    const CONFIG_SEND_EMAIL_ADMIN  = 'itoris_pricealert/general/send_email_admin';
    const CONFIG_SENDER_ADMIN_EMAIL  = 'itoris_pricealert/general/sender_admin_email';
    const CONFIG_TEMPLATE_ADMIN_EMAIL  = 'itoris_pricealert/general/template_admin_email';
    const CONFIG_SENDER_CUSTOMER_EMAIL  = 'itoris_pricealert/general/sender_customer_email';
    const CONFIG_TEMPLATE_CUSTOMER_EMAIL  = 'itoris_pricealert/general/template_customer_email';
    const CONFIG_GROUP_LIST  = 'itoris_pricealert/general/group_list';
    const CONFIG_LINK_TEXT  = 'itoris_pricealert/general/link_text';
    const CONFIG_COMMENT_POPUP  = 'itoris_pricealert/general/comment_popup';

    public $magentoConfigTable = 'core_config_data';

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {

        $setup->startSetup();
        $setup->run("
                CREATE TABLE IF NOT EXISTS {$setup->getTable('itoris_pricematch_data')} (
                    `item_id` int unsigned not null auto_increment primary key,
                    `product_id` int unsigned not null,
                    `date_created` varchar(20) not null,
                    `customer_id` int unsigned,
                    `store_id` smallint unsigned not null,
                    `status` varchar(255),
                    `email` varchar(320),
                    `name` varchar(320),
                    `match_url` text,
                    `comment` text(2000),
                    `response` text,
                    `date_response` varchar(20),
                    `method` varchar(255),
                    `match_price` decimal(12,4),
                    `old_price` decimal(12,4),
                    `by_request` text,
                    `coupon_id` int(10) unsigned,
                    foreign key (`store_id`) references {$setup->getTable('store')} (`store_id`) on delete cascade on update cascade,
                    foreign key (`customer_id`) references {$setup->getTable('customer_entity')} (`entity_id`) on delete cascade on update cascade,
                    foreign key (`product_id`) references {$setup->getTable('catalog_product_entity')} (`entity_id`) on delete cascade on update cascade,
                    foreign key (`coupon_id`) references {$setup->getTable('salesrule_coupon')} (`coupon_id`) on delete cascade on update cascade
                )engine = InnoDB default charset = utf8;
            ");

        $setup->run("CREATE INDEX itoris_pricematch_data_product_id_index ON {$setup->getTable('itoris_pricematch_data')}(`product_id`);");
        $setup->run("CREATE INDEX itoris_pricematch_data_customer_id_index ON {$setup->getTable('itoris_pricematch_data')}(`customer_id`);");

        $setup->run("
                CREATE TABLE IF NOT EXISTS {$setup->getTable('itoris_pricematch_setting')} (
                    `item_id` int unsigned not null auto_increment primary key,
                    `product_id` int unsigned not null,
                    `attr_code` varchar(255) not null,
                    `store_id` smallint unsigned not null,
                    `value` text,
                    foreign key (`store_id`) references {$setup->getTable('store')} (`store_id`) on delete cascade on update cascade,
                    foreign key (`product_id`) references {$setup->getTable('catalog_product_entity')} (`entity_id`) on delete cascade on update cascade
                )engine = InnoDB default charset = utf8;
            ");
        $setup->run("CREATE INDEX itoris_pricematch_setting_index ON {$setup->getTable('itoris_pricematch_setting')}(`product_id`, `store_id` );");

        $setup->run("INSERT INTO {$setup->getTable($this->magentoConfigTable)} (`scope`, `scope_id`, `path`, `value`) VALUES ('default', 0, '". HelperData::MODULE_ENABLED."', '1');");
        $setup->run("INSERT INTO {$setup->getTable($this->magentoConfigTable)} (`scope`, `scope_id`, `path`, `value`) VALUES ('default', 0, '". HelperData::CONFIG_SEND_EMAIL_ADMIN."', '0');");
        $setup->run("INSERT INTO {$setup->getTable($this->magentoConfigTable)} (`scope`, `scope_id`, `path`, `value`) VALUES ('default', 0, '". HelperData::CONFIG_SENDER_ADMIN_EMAIL."', '');");
        $setup->run("INSERT INTO {$setup->getTable($this->magentoConfigTable)} (`scope`, `scope_id`, `path`, `value`) VALUES ('default', 0, '". HelperData::CONFIG_TEMPLATE_ADMIN_EMAIL."', 'itoris_pricematch_general_template_admin_email');");
        $setup->run("INSERT INTO {$setup->getTable($this->magentoConfigTable)} (`scope`, `scope_id`, `path`, `value`) VALUES ('default', 0, '". HelperData::CONFIG_SENDER_CUSTOMER_EMAIL."', '0');");
        $setup->run("INSERT INTO {$setup->getTable($this->magentoConfigTable)} (`scope`, `scope_id`, `path`, `value`) VALUES ('default', 0, '". HelperData::CONFIG_TEMPLATE_CUSTOMER_EMAIL."', 'itoris_pricematch_general_template_customer_email');");
        $setup->run("INSERT INTO {$setup->getTable($this->magentoConfigTable)} (`scope`, `scope_id`, `path`, `value`) VALUES ('default', 0, '". HelperData::CONFIG_GROUP_LIST."', '-1');");
        $linkText = "Found a better price? Let us know and we'll match it.";
        $commentPopUp = "We're never beaten on price. Tell us where you found a lower rate. Please fill in the form below and we will respond to you shortly.";
        $setup->run("INSERT INTO {$setup->getTable($this->magentoConfigTable)} (`scope`, `scope_id`, `path`, `value`) VALUES ('default', 0, '". HelperData::CONFIG_LINK_TEXT."',". $setup->getConnection()->quote($linkText) .");");
        $setup->run("INSERT INTO {$setup->getTable($this->magentoConfigTable)} (`scope`, `scope_id`, `path`, `value`) VALUES ('default', 0, '". HelperData::CONFIG_COMMENT_POPUP."',". $setup->getConnection()->quote($commentPopUp).");");

        $setup->endSetup();
    }
}
