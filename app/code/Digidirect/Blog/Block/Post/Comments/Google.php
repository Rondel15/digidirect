<?php
namespace Digidirect\Blog\Block\Post\Comments;

use Digidirect\Blog\Helper\Data;
use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session;
use Magento\Framework\Registry;

/**
 * Class Google
 */
class Google extends AbstractCommentType
{
    const FIRST_PART_PROPERTY = 'BLOGGER';
    
    const VIEW_TYPE = 'FILTERED_POSTMOD';

    /**
     * @return string
     */
    public function getFirstPartProperty()
    {
        return self::FIRST_PART_PROPERTY;
    }

    /**
     * @return string
     */
    public function getViewType()
    {
        return self::VIEW_TYPE;
    }
}
