<?php
namespace Digidirect\Blog\Controller\Adminhtml\Post;

/**
 * Class Posts
 */
class Posts extends AbstractAjaxGird
{
    /**
     * @return string
     */
    protected function getBlockName()
    {
        return 'admin.digidirect_blog.related_posts.grid';
    }
}
