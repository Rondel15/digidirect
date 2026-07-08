<?php

namespace Digidirect\Localization\Plugin\Checkout\Model;

use Digidirect\Localization\Helper\Data;

class LayoutProcessor
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * LayoutProcessor constructor.
     *
     * @param Data $data
     */
    public function __construct(Data $data)
    {
        $this->helper = $data;
    }

    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array $jsLayout
    ) {
        if (!$this->helper->isPhoneSuggestionEnabled()) {
            return $jsLayout;
        }
        $phoneComponentPath = 'Digidirect_Localization/js/checkout/localization-phone';
        $validation = $jsLayout['components']['checkout']['children']['steps']['children']
            ['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']
            ['telephone']['validation'] ?? ['required_entry' => 'true'];
        $validation['required_entry'] = true;
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone'] = [
            'component' => $phoneComponentPath,
            'config' => [
                'customScope' => 'shippingAddress',
                'template' => 'Digidirect_Localization/form/field',
                'elementTmpl' => 'Digidirect_Localization/form/element/input',
                'elementSelectTmpl' => 'Digidirect_Localization/form/element/select',
                'options' => [],
                'tooltip' => [
                    'description' => __('For delivery questions.'),
                ],
            ],
            'dataScope' => 'shippingAddress.telephone',
            'label' => __('Phone Number'),
            'provider' => 'checkoutProvider',
            'visible' => true,
            'validation' => $validation,

            'sortOrder' => (int)($jsLayout['components']['checkout']['children']['steps']['children']
                ['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']
                ['telephone']['sortOrder'] ?? 250),
        ];

        $validation = $jsLayout['components']['checkout']['children']['steps']['children']
            ['shipping-step']['children']['shippingAddress']['popUpForm']['children']
            ['shipping-address-fieldset']['children']['telephone']['validation']  ?? ['required-entry' => true];
        $validation['required_entry'] = true;

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['popUpForm']['children']['shipping-address-fieldset']['children']['telephone'] = [
            'component' => $phoneComponentPath,
            'config' => [
                'customScope' => 'shippingAddress',
                'template' => 'Digidirect_Localization/form/field',
                'elementTmpl' => 'Digidirect_Localization/form/element/input',
                'elementSelectTmpl' => 'Digidirect_Localization/form/element/select',
                'options' => [],
                'tooltip' => [
                    'description' => __('For delivery questions.'),
                ],
            ],
            'dataScope' => 'shippingAddress.telephone',
            'label' => __('Phone Number'),
            'provider' => 'checkoutProvider',
            'visible' => true,
            'validation' => $validation,

            'sortOrder' => (int)($jsLayout['components']['checkout']['children']['steps']['children']
                ['shipping-step']['children']['shippingAddress']['popUpForm']['children']
                ['shipping-address-fieldset']['children']['telephone']['sortOrder'] ?? 250),
        ];

        $configuration = $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
        ['children']['payment']['children']['payments-list']['children'];
        $componentPath = 'Magento_Checkout/js/view/billing-address';
        foreach ($configuration as $paymentGroup => $groupConfig) {
            if (isset($groupConfig['component']) and $groupConfig['component'] === $componentPath) {
                $paymentCode = substr($paymentGroup, 0, -5);

                $validation = $jsLayout['components']['checkout']['children']['steps']['children']
                    ['billing-step']['children']['payment']['children']['payments-list']
                    ['children'][$paymentGroup]['children']['form-fields']['children']
                    ['telephone']['validation'] ?? ['required-entry' => true];
                $validation['required_entry'] = true;

                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$paymentGroup]
                ['children']['form-fields']['children']['telephone'] = [
                    'component' => $phoneComponentPath,
                    'config' => [
                        'customScope' => 'billingAddress' . $paymentCode,
                        'template' => 'Digidirect_Localization/form/field',
                        'elementTmpl' => 'Digidirect_Localization/form/element/input',
                        'elementSelectTmpl' => 'Digidirect_Localization/form/element/select',
                        'options' => [],
                        'tooltip' => [
                            'description' => __('For delivery questions.'),
                        ],
                    ],
                    'dataScope' => 'billingAddress' . $paymentCode . '.telephone',
                    'label' => __('Phone Number'),
                    'provider' => 'checkoutProvider',
                    'visible' => true,
                    'validation' => $validation,

                    'sortOrder' => (int)($jsLayout['components']['checkout']['children']['steps']['children']
                        ['billing-step']['children']['payment']['children']['payments-list']
                        ['children'][$paymentGroup]['children']['form-fields']['children']
                        ['telephone']['sortOrder'] ?? 250),
                ];
            }
        }
        return $jsLayout;
    }
}
