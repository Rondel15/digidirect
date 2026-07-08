<?php

namespace Digidirect\Blog\Sql;

use Digidirect\Blog\Model\ResourceModel\Category;

/**
 * Try to unify tables aliases during sql queries
 */
class Aliases
{
    /**
     * Category information table alias for store and default store
     */
    const CATEGORY_INFORMATION_TABLE_ALIAS = Category::DIGIDIRECT_BLOG_CATEGORY_INFORMATION_TABLE;
    const CATEGORY_INFORMATION_TABLE_DEFAULT_CONTENT_ALIAS
        = CategoryInformationJoin::DEFAULT_STORE_COLUMN_PREFIX . self::CATEGORY_INFORMATION_TABLE_ALIAS;

    /**
     * Category entity table alias
     */
    const CATEGORY_ENTITY_TABLE_ALIAS = Category::DIGIDIRECT_BLOG_CATEGORY_TABLE;
}
