<?php
namespace Digidirect\ExtendedShippingRates\Block\Adminhtml\ExtendedShippingRates\Quote\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Digidirect\ExtendedShippingRates\Ui\DataProvider\Quote\Form\QuoteDataProvider;

class Time extends Generic implements TabInterface
{
    /**
     * @var string
     */
    protected $_nameInLayout = 'rule_actions';

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Rule Information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Rule Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry(\Digidirect\ExtendedShippingRates\Model\Rule::CURRENT_PROMO_QUOTE_RULE);

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        $fieldset = $form->addFieldset('time_fieldset', ['legend' => __('Time')]);

        $useTimeTrigger = $fieldset->addField(
            'use_time',
            'checkbox',
            [
                'name' => 'use_time',
                'label' => __('Use Time'),
                'title' => __('Use Time'),
                'onchange' => 'changeUseTime(this)',
                'values' => [
                    ['value' => '1', 'label' => __('Yes')]
                ],
                'checked' => (int)$model->getData('use_time'),
                'data-form-part' => QuoteDataProvider::FORM_NAME
            ]
        );

        $useTimeTrigger->setAfterElementHtml(
            "<script>
                require(['jquery', 'jquery/ui'], function($){
                    $('input[name=" . $useTimeTrigger->getName() . "]').trigger('change');
                });
                function changeUseTime(selectItem){
                    var item = jQuery(selectItem);
                    if (item.is(':checked')) {
                        jQuery('.field-time_range').show();
                        jQuery('.field-time_enabled').show();
                    } else {
                        jQuery('.field-time_range').hide();
                        jQuery('.field-time_enabled').hide();
                    }
                }
            </script>"
        );

        $time = $fieldset->addField(
            'time',
            'time',
            [
                'name' => 'time',
                'label' => __('Time'),
                'title' => __('Time')
            ]
        );
        $renderer = $this->getLayout()->createBlock(
            'Digidirect\ExtendedShippingRates\Block\Adminhtml\ExtendedShippingRates\Widget\TimeSlider'
        );
        /** @var \Magento\Framework\Data\Form\Element\Renderer\RendererInterface $renderer */
        $time->setRenderer($renderer);
        $time->getRenderer()->setRule($model);

        $fieldset->addField(
            'time_enabled',
            'select',
            [
                'name' => 'time_enabled',
                'label' => __('Time when Rule is'),
                'title' => __('Time when Rule is'),
                'values' => [
                    1 => [
                        'value' => 1,
                        'label' => 'Functioning',
                    ],
                    2 => [
                        'value' => 0,
                        'label' => 'Not Functioning',
                    ]
                ],
                'data-form-part' => QuoteDataProvider::FORM_NAME
            ]
        );

        $form->setValues($model->getData());

        if ($model->isReadonly()) {
            foreach ($fieldset->getElements() as $element) {
                $element->setReadonly(true, true);
            }
        }

        $this->setForm($form);

        $this->_eventManager->dispatch(
            'adminhtml_extendedshippingrates_quote_edit_tab_time_prepare_form',
            ['form' => $form]
        );

        return parent::_prepareForm();
    }
}
