<?php

namespace Digidirect\Digi\Rewrite\Digidirect\MyStoreWidget\Model;

use Digidirect\MyStoreWidget\Model\ResourceModel\MyStoreIndex;

class MyStoreIndexRewrite extends MyStoreIndex
{
    /**
     * @param array $attributes
     * @param int $storeId
     * @return array
     */
    protected function getFulltextColumns(array $attributes, $storeId)
    {
        $connection = $this->getConnection();
        $table = $this->getTableName($storeId);

        $tableStructure = $connection->describeTable($table);
        $columns = [];
        foreach ($attributes as $attribute) {
            if (isset($tableStructure[$attribute])
                && in_array($tableStructure[$attribute]['DATA_TYPE'], ['varchar', 'text', 'longtext'])
            ) {
                $columns[] = $attribute;
            }
        }

        return $columns;
    }
}
