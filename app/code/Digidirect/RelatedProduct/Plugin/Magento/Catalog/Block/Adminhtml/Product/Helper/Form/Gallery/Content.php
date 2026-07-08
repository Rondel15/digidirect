<?php
namespace Digidirect\RelatedProduct\Plugin\Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery;

use Digidirect\RelatedProduct\Block\Adminhtml\Product\Helper\Form\Gallery\Content as GalleryContent;

/**
 * Class Content
 * @author Digidirect team
 * @package Digidirect\RelatedProduct\Block\Adminhtml\Product\Helper\Form\Gallery
 */
class Content
{
    /**
     * @param GalleryContent $galleryBlock
     * @return void
     */
    public function beforeToHtml(GalleryContent $galleryBlock)
    {
        if ($galleryBlock->canShow()) {
            $galleryBlock->setTemplate('Digidirect_RelatedProduct::helper/gallery.phtml');
        }
    }
}
