<?php

namespace Digidirect\Blog\Sql;

use Magento\Framework\DB\Select;

/**
 * Join information by store and id if required
 *
 * @since 2.0.0
 */
interface InformationJoinInterface
{
    /**
     * @param Select $select
     * @param int $storeId
     * @param string $mainTableAlias
     * @param null $id
     * @param array $fields
     * @return Select
     */
    public function join(Select $select, int $storeId, $mainTableAlias = null, $id = null, $fields = []);
}
