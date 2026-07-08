<?php
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_M2_ITORIS_PRICE_MATCH
 * @copyright  Copyright (c) 2018 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

namespace Itoris\PriceMatch\Ui\Component;

class Actions extends \Magento\Ui\Component\Listing\Columns\Column
{

    private $urlBuilder;

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                $item[$name]['edit'] = [
                    'href' => $this->urlBuilder->getUrl('itorispm/index/edit', ['id' => $item['item_id']]),
                    'label' => __('Edit'),
                ];

                if (isset($item['item_id'])) {
                    if ($item['status'] == 'pending') {
                        $item[$name]['rejected'] = [
                            'href' => $this->urlBuilder->getUrl('itorispm/action/reject', ['id' => $item['item_id']]),
                            'label' => __('Reject'),
                            'confirm' => [
                                'title' => __('Reject Price Match request'),
                                'message' => __('Are you sure want to reject the Price Match Request?')
                            ]
                        ];
                    }

                    $item[$name]['remove'] = [
                        'href' => $this->urlBuilder->getUrl('itorispm/action/delete', ['id' => $item['item_id']]),
                        'label' => __('Remove'),
                        'confirm' => [
                            'title' => __('Remove Price Match request'),
                            'message' => __('Are you sure want to remove the Price Match Request?')
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}
