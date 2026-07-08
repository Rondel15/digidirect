<?php
namespace Digidirect\Blog\Model\Config\Provider;

use Digidirect\Blog\Model\Config\SourceProviderInterface;

/**
 * Class PageLayout
 */
class PageLayout implements SourceProviderInterface
{
    /**
     * @return array
     */
    public function getOptions()
    {
        return [
            [
                'value' => '1column',
                'label' => __('1 column')
            ],
            [
                'value' => '2columns-left',
                'label' => __('2 columns Left Side Bar')
            ],
            [
                'value' => '2columns-right',
                'label' => __('2 columns Right Side Bar')
            ],
            [
                'value' => '3columns',
                'label' => __('3 columns')
            ]
        ];
    }
}
