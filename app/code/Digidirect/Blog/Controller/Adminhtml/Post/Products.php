<?php
namespace Digidirect\Blog\Controller\Adminhtml\Post;

/**
 * Class Products
 */
class Products extends AbstractAjaxGird
{
    /**
     * @return string
     */
    protected function getBlockName()
    {
        return 'admin.digidirect_blog.related_products.grid';
    }
}
