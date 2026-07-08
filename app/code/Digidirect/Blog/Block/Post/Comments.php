<?php
namespace Digidirect\Blog\Block\Post;

use Digidirect\Blog\Block\Blog;

/**
 * Class Comments
 */
class Comments extends Blog
{
    /**
     * @return string
     */
    public function getCommentsHtml()
    {
        $block = $this->getChildBlock($this->getCommentType() . '.comment.type');
        if (is_object($block)) {
            return $block->toHtml();
        }
        return '';
    }

    /**
     * @return bool
     */
    public function isCommentsEnabled()
    {
        return !empty($this->getCommentType());
    }

    /**
     * @return string
     */
    public function getCommentType()
    {
        return $this->dataHelper->getCommentSettingsConfig('type_of_comment');
    }

    /**
     * @return bool|int|null
     */
    public function getCacheLifetime()
    {
        $cacheLifetime = parent::getCacheLifetime();
        if (!$cacheLifetime) {
            $cacheLifetime = 86400;
        }

        return $cacheLifetime;
    }

    /**
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $cacheKey = parent::getCacheKeyInfo();
        $cacheKey['nil'] = $this->getNameInLayout();
        $cacheKey['request_params'] = json_encode($this->getRequest()->getParams());
        return $cacheKey;
    }
}
