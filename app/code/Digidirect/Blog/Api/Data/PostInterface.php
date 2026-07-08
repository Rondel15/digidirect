<?php
namespace Digidirect\Blog\Api\Data;

/**
 * Interface PostInterface
 */
interface PostInterface
{
    const CURRENT_ITEM = 'current_blog_post';

    const PREPARED_IMAGES_KEY = 'blog_images';

    const FIELD_ID = 'entity_id';

    const FIELD_STATUS = 'status';
    
    const FIELD_PUBLISH_DATE = 'publish_date';

    const FIELD_UPDATED_AT = 'updated_at';

    const DIGIDIRECT_BLOG_POST_TABLE = 'digidirect_blog_post';
}
