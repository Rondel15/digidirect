<?php
namespace Digidirect\Blog\Model\Config\Provider;

use Digidirect\Blog\Model\Config\SourceProviderInterface;

/**
 * Class CommentType
 */
class CommentType implements SourceProviderInterface
{
    /**
     * @return array
     */
    public function getOptions()
    {
        return [
            ['value' => '0', 'label' => __('Disabled')],
            ['value' => 'default', 'label' => __('Default ')],
            ['value' => 'facebook', 'label' => __('Facebook')],
            ['value' => 'google', 'label' => __('Google')],
            ['value' => 'disqus', 'label' => __('Disqus')],
        ];
    }
}
