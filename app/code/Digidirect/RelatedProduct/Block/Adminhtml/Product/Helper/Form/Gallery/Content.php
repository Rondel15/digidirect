<?php
namespace Digidirect\RelatedProduct\Block\Adminhtml\Product\Helper\Form\Gallery;

use Digidirect\RelatedProduct\Helper\RangeProduct as RangeProductHelper;
use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery\Content as GalleryContent;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;

/**
 * Class Content
 * @author Digidirect team
 * @package Digidirect\RelatedProduct\Block\Adminhtml\Product\Helper\Form\Gallery
 */
class Content extends GalleryContent
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var RangeProductHelper
     */
    protected $rangeProductHelper;

    /**
     * Content constructor.
     * @param Context $context
     * @param EncoderInterface $jsonEncoder
     * @param Config $mediaConfig
     * @param Registry $registry
     * @param RangeProductHelper $rangeProductHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        Config $mediaConfig,
        Registry $registry,
        RangeProductHelper $rangeProductHelper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->rangeProductHelper = $rangeProductHelper;
        parent::__construct($context, $jsonEncoder, $mediaConfig, $data);
    }

    /**
     * @return bool
     */
    public function canShow()
    {
        $product = $this->registry->registry('current_product');
        if ($product) {
            return $this->rangeProductHelper->isRangeProduct($product);
        }
        return false;
    }
}
