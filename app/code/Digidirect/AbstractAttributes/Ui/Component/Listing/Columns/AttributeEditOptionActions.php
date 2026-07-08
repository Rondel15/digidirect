<?php
namespace Digidirect\AbstractAttributes\Ui\Component\Listing\Columns;

/**
 * Class AttributeEditOptionActions
 * @package Digidirect\AbstractAttributes\Ui\Component\Listing\Columns
 */
class AttributeEditOptionActions extends OptionActions
{
    /**
     * Url path
     */
    const URL_PATH_EDIT = 'eaa/option/editBackAttribute';

    /**
     * Get url params
     * @param array $item
     * @return \string[]
     */
    protected function _getEditUrlParams(array $item)
    {
        return [
            'option_id'    => $item['option_id'],
            'attribute_id' => $this->context->getRequestParam('attribute_id')
        ];
    }

    /**
     * Prepare Data Source
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['option_id'])) {
                    $item[$this->getData('name')] = [
                        'edit'   => [
                            'href'  => $this->urlBuilder->getUrl(
                                static::URL_PATH_EDIT,
                                $this->_getEditUrlParams($item)
                            ),
                            'label' => __('Edit')
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}
