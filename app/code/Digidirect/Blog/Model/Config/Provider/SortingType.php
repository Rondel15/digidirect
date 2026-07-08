<?php
namespace Digidirect\Blog\Model\Config\Provider;

use Digidirect\Blog\Model\Config\SourceProviderInterface;

/**
 * Class SortingType
 */
class SortingType implements SourceProviderInterface
{
    const SORT_DESC = 'desc';
    const SORT_ASC = 'asc';

    /**
     * @return array
     */
    public function getOptions()
    {
        return [
            [
                'value' => self::SORT_DESC,
                'label' => __('Newest First')
            ],
            [
                'value' => self::SORT_ASC,
                'label' => __('Oldest First')
            ]
        ];
    }
}
