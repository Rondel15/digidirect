<?php
namespace Digidirect\Blog\Api\Data;

/**
 * Interface CommentInterface
 */
interface CommentInterface
{
    const CURRENT_ITEM = 'current_blog_comment';
    
    const XML_PATH_EMAIL_RECIPIENT = 'digidirect_blog/comments/admin_email';

    const FIELD_POST_ID = 'post_id';
    
    const FIELD_COMMENT_STATUS = 'comment_status';
    
    const FIELD_COMMENT_DATE = 'comment_date';
}
