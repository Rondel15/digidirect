<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture(
    'Magento/PurchaseOrderRule/_files/company_with_purchase_order_multiple_approvers_roles_single_rule_rollback.php'
);
