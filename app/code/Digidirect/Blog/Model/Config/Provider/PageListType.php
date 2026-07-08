<?php
namespace Digidirect\Blog\Model\Config\Provider;

use Digidirect\Blog\Model\Config\SourceProviderInterface;

/**
 * Class PageListType
 */
class PageListType implements SourceProviderInterface
{
    const TYPE_GRID = 'grid';
    const TYPE_LIST = 'list';
    
    /**
     * @return array
     */
    public function getOptions()
    {
        return [
            ['value' => self::TYPE_GRID, 'label' => __('Grid')],
            ['value' => self::TYPE_LIST, 'label' => __('List')]
        ];
    }
}
