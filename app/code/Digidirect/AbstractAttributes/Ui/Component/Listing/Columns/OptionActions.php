<?php
namespace Digidirect\AbstractAttributes\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Store\Model\Store;

/**
 * Class OptionActions
 * @package Digidirect\AbstractAttributes\Ui\Component\Listing\Columns
 */
class OptionActions extends Column
{
    /**
     * Url path
     */
    const URL_PATH_EDIT = 'eaa/option/edit';
    const URL_PATH_DELETE = 'eaa/option/delete';
    const URL_PATH_DETAILS = 'eaa/option/details';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * OptionActions constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;

        parent::__construct($context, $uiComponentFactory, $components, $data);
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
                        ],
                        'delete' => [
                            'href'    => $this->urlBuilder->getUrl(
                                static::URL_PATH_DELETE,
                                $this->_getDeleteUrlParams($item)
                            ),
                            'label'   => __('Delete'),
                            'confirm' => [
                                'title'   => __('Delete "${ $.$data.label_0 }"'),
                                'message' => __('Are you sure you wan\'t to delete a "${ $.$data.label_0 }" record?')
                            ]
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }

    /**
     * Get url params
     * @param array $item
     * @return \string[]
     */
    protected function _getEditUrlParams(array $item)
    {
        return [
            'option_id' => $item['option_id'],
            'store' => Store::DEFAULT_STORE_ID,
        ];
    }

    /**
     * Get url params
     * @param array $item
     * @return \string[]
     */
    protected function _getDeleteUrlParams(array $item)
    {
        return [
            'option_id' => $item['option_id']
        ];
    }
}
