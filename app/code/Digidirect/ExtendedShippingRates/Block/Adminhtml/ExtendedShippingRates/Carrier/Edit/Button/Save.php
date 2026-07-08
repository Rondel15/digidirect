<?php
namespace Digidirect\ExtendedShippingRates\Block\Adminhtml\ExtendedShippingRates\Carrier\Edit\Button;

use Magento\Ui\Component\Control\Container;

/**
 * Class Save
 */
class Save extends Generic
{
    const TARGET_FORM_NAME = 'digidirect_extendedshippingrates_carrier_form.digidirect_extendedshippingrates_carrier_form';

    /**
     * Get save button data with options: save & new; save & close;
     *
     * @return array
     */
    public function getButtonData()
    {
        $options = $this->getOptions();
        $data = [
            'label' => __('Save'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => self::TARGET_FORM_NAME,
                                'actionName' => 'save',
                                'params' => [
                                    false
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'options' => $options,
            'class_name' => Container::SPLIT_BUTTON,
        ];

        return $data;
    }

    /**
     * Retrieve options
     *
     * @return array
     */
    protected function getOptions()
    {
        $options[] = [
            'label' => __('Save & New'),
            'id_hard' => 'save_and_new',
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'actionName' => 'save',
                                'params' => [
                                    true,
                                    [
                                        'back' => 'newAction'
                                    ]
                                ],
                                'targetName' => self::TARGET_FORM_NAME,
                            ]
                        ]
                    ]
                ]
            ],
        ];

        $options[] = [
            'label' => __('Save & Close'),
            'id_hard' => 'save_and_close',
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'actionName' => 'save',
                                'params' => [
                                    true
                                ],
                                'targetName' => self::TARGET_FORM_NAME,
                            ]
                        ]
                    ]
                ]
            ],
        ];

        return $options;
    }
}
