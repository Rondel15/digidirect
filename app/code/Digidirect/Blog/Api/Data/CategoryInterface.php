<?php

namespace Digidirect\Blog\Api\Data;

/**
 * Interface CategoryInterface
 */
interface CategoryInterface extends StoreViewSpecificInterface
{
    const CURRENT_ITEM = 'current_blog_category';

    const FIELD_STATUS = 'status';

    const FIELD_URL_KEY = 'url_key';

    const ROOT_CATEGORY_ID = 0;

    const CONTENT_STORE_ID = 'content_store_id';

    const FIELD_UPDATED_AT = 'updated_at';
}
