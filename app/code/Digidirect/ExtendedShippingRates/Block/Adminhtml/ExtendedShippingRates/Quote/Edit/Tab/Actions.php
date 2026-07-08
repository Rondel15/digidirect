<?php
namespace Digidirect\ExtendedShippingRates\Block\Adminhtml\ExtendedShippingRates\Quote\Edit\Tab;

use Digidirect\ExtendedShippingRates\Api\Data\RuleInterface;
use Digidirect\ExtendedShippingRates\Model\Config\Source\Rule\ActionTypeOptions;
use Digidirect\ExtendedShippingRates\Model\Config\Source\Shipping\ExtendedActions as ShippingActionsConfig;
use Digidirect\ExtendedShippingRates\Model\Config\Source\Shipping\Methods as Config;
use Digidirect\ExtendedShippingRates\Model\Rule;
use Digidirect\ExtendedShippingRates\Ui\DataProvider\Quote\Form\QuoteDataProvider;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\Convert\DataObject as ObjectConverter;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;

/**
 * Class Actions
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @package Digidirect\ExtendedShippingRates\Block\Adminhtml\ExtendedShippingRates\Quote\Edit\Tab
 */
class Actions extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    const HIDDEN_FIELDSET_CLASS_NAME = 'hidden';

    /**
     * Core registry
     *
     * @var Fieldset
     */
    protected $rendererFieldset;

    /** @var Config */
    protected $shippingConfig;

    /** @var Config */
    protected $shippingActionsConfig;

    /** @var \Magento\Framework\Convert\DataObject */
    protected $objectConverter;

    /** @var Yesno */
    protected $yesNoConfig;

    /** @var \Digidirect\ExtendedShippingRates\Model\Rule */
    protected $sourceModel;

    /**
     * @var ActionTypeOptions
     */
    protected $actionTypeOptions;

    /**
     * Actions constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Fieldset $rendererFieldset
     * @param Config $config
     * @param ObjectConverter $objectConverter
     * @param ShippingActionsConfig $shippingActionsConfig
     * @param Yesno $yesno
     * @param ActionTypeOptions $actionTypeOptions
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Fieldset $rendererFieldset,
        Config $config,
        ObjectConverter $objectConverter,
        ShippingActionsConfig $shippingActionsConfig,
        Yesno $yesno,
        ActionTypeOptions $actionTypeOptions,
        array $data = []
    ) {
        $this->rendererFieldset = $rendererFieldset;
        $this->shippingConfig = $config;
        $this->shippingActionsConfig = $shippingActionsConfig;
        $this->objectConverter = $objectConverter;
        $this->yesNoConfig = $yesno;
        $this->actionTypeOptions = $actionTypeOptions;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Actions');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Actions');
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
     * Prepare amounts for form
     *
     * @param Rule $model
     * @return Rule
     */
    protected function _prepareModel(Rule $model)
    {
        $amounts = $model->getAmount();

        if (empty($amounts)) {
            return $model;
        }

        foreach ($amounts as $amountKey => $amountData) {
            $valueKey = 'amount_' . $amountKey . '_value';
            $value = (double)$amountData['value'];
            $model->setData($valueKey, $value);

            $sortOrderKey = 'amount_' . $amountKey . '_sort';
            $sortOrderValue = (double)$amountData['sort'];
            $model->setData($sortOrderKey, $sortOrderValue);
        }

        return $model;
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry(Rule::CURRENT_PROMO_QUOTE_RULE);
        $model = $this->_prepareModel($model);
        $this->sourceModel = $model;
        $htmlIdPrefix = 'rule_';

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix($htmlIdPrefix);

        $this->addMainFieldset($form);
        $this->addModifyCostFieldset($form);
        $this->addHideShippingMethodsFieldset($form);
        $this->addUseAltTitleShippingMethodsFieldset($form);

        $this->rendererFieldset->setTemplate(
            'Magento_CatalogRule::promo/fieldset.phtml'
        )->setNewChildUrl(
            $this->getUrl(
                'digidirect_extendedshippingrates/extendedshippingrates_quote/newActionHtml/form/rule_actions_fieldset'
            )
        );
        if (!$model->getId()) {
            $model->setData(RuleInterface::ACTION_TYPE_OPTION, 1);
        }

        $this->_eventManager->dispatch('adminhtml_block_extendedshippingrates_actions_prepareform', ['form' => $form]);

        $form->setValues($model->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Add the fieldset with ability to hide selected shipping methods
     *
     * @param \Magento\Framework\Data\Form $form
     * @return mixed
     */
    protected function addHideShippingMethodsFieldset(\Magento\Framework\Data\Form $form)
    {
        $modelActionType = $this->getSourceModelActionType();
        $hidden = !in_array(Rule::ACTION_DISABLE_SM, $modelActionType) ? self::HIDDEN_FIELDSET_CLASS_NAME : '';
        $classes = 'dependable_fieldset_' . Rule::ACTION_DISABLE_SM . ' ' . $hidden;
        $hideSMFieldset = $form->addFieldset(
            'hide_shipping_method_fieldset',
            ['legend' => __('Show/Hide Shipping Method'), 'class' => $classes]
        );

        $shippingmethods = $this->getShippingMethods();
        $hideSMFieldset->addField(
            RuleInterface::DISABLED_SHIPPING_METHODS,
            'multiselect',
            [
                'name' => 'disabled_shipping_methods[]',
                'label' => __('Shipping Methods'),
                'title' => __('Shipping Methods'),
                'values' => $shippingmethods,
                'style' => 'display:block',
                'data-form-part' => QuoteDataProvider::FORM_NAME
            ]
        );

        return $hideSMFieldset;
    }

    /**
     * Add the modify cost fieldset
     *
     * @param \Magento\Framework\Data\Form $form
     * @return mixed
     */
    protected function addModifyCostFieldset(\Magento\Framework\Data\Form $form)
    {
        $modelActionType = $this->getSourceModelActionType();
        $hidden = !in_array(Rule::ACTION_OVERWRITE_COST, $modelActionType) ? self::HIDDEN_FIELDSET_CLASS_NAME : '';
        $classes = 'dependable_fieldset_' . Rule::ACTION_OVERWRITE_COST . ' ' . $hidden;
        $modifyCostFieldset = $form->addFieldset(
            'modify_cost_fieldset',
            ['legend' => __('Modify Shipping Cost'), 'class' => $classes]
        );

        // How calculate
        $actions = $this->shippingActionsConfig->toOptionArray();
        $simpleActionField = $modifyCostFieldset->addField(
            RuleInterface::SIMPLE_ACTION,
            'multiselect',
            [
                'label' => __('Method'),
                'name' => RuleInterface::SIMPLE_ACTION,
                'onchange' => 'changeAmounts(this)',
                'values' => $actions,
                'style' => 'display:block',
                'data-form-part' => QuoteDataProvider::FORM_NAME
            ]
        );

        $simpleActionField->setAfterElementHtml(
            "<script>
                require(['jquery', 'jquery/ui'], function($){
                    $('#rule_simple_action').trigger('change');
                });
    
                function changeAmounts(e) {
    
                    var values = jQuery(e).val();
    
                    try {
                        jQuery('.amount-field').each(function() {
                            jQuery(this).closest('.admin__field').hide();
                        });
    
                        jQuery(values).each(function(){
                            var className = '.amount-field.'+this;
                            jQuery(className).closest('.admin__field').show();
                        });
                    } catch (e) {
                        console.log(e);
                    }
                }
            </script>"
        );

        $this->_addAmountFields($actions, $modifyCostFieldset, $this->sourceModel);

        // All available shipping methods
        $shippingmethods = $this->getShippingMethods();
        $modifyCostFieldset->addField(
            RuleInterface::SHIPPING_METHODS,
            'multiselect',
            [
                'name' => 'shipping_methods[]',
                'label' => __('Apply to Shipping Methods'),
                'title' => __('Apply to Shipping Methods'),
                'values' => $shippingmethods,
                'style' => 'display:block',
                'data-form-part' => QuoteDataProvider::FORM_NAME
            ]
        );

        return $modifyCostFieldset;
    }

    /**
     * Add use alternative title fieldset
     *
     * @param \Magento\Framework\Data\Form $form
     * @return mixed
     */
    protected function addUseAltTitleShippingMethodsFieldset($form)
    {
        $modelActionType = $this->getSourceModelActionType();
        $hidden = !in_array(Rule::ACTION_USE_ALT_TITLE, $modelActionType) ? self::HIDDEN_FIELDSET_CLASS_NAME : '';
        $classes = 'dependable_fieldset_' . Rule::ACTION_USE_ALT_TITLE . ' ' . $hidden;
        $useAltTitleFieldset = $form->addFieldset(
            'use_alt_title_shipping_method_fieldset',
            ['legend' => __('Use Shipping Method Alternative Title'), 'class' => $classes]
        );

        $shippingmethods = $this->getShippingMethods();
        $useAltTitleFieldset->addField(
            Rule::USED_ALT_TITLE_SHIPPING_METHODS,
            'multiselect',
            [
                'name' => 'used_alt_title_shipping_methods[]',
                'label' => __('Apply to Shipping Methods'),
                'title' => __('Apply to Shipping Methods'),
                'style' => 'display:block',
                'values' => $shippingmethods,
                'data-form-part' => QuoteDataProvider::FORM_NAME
            ]
        );

        return $useAltTitleFieldset;
    }

    /**
     * Add main fieldset with action type and stop future rules processing select.
     *
     * @param \Magento\Framework\Data\Form $form
     * @return mixed
     */
    protected function addMainFieldset(\Magento\Framework\Data\Form $form)
    {
        $mainFieldset = $form->addFieldset(
            'action_fieldset',
            ['legend' => __('Rule\'s Action')]
        );

        // What to do
        $actionType = $mainFieldset->addField(
            RuleInterface::ACTION_TYPE,
            'multiselect',
            [
                'label' => __('Type'),
                'name' => 'action_type[]',
                'required' => true,
                'onchange' => 'changeActions(this)',
                'style' => 'display:block',
                'values' => [
                    ['value' => Rule::ACTION_OVERWRITE_COST, 'label' => __('Modify Shipping Cost')],
                    ['value' => Rule::ACTION_DISABLE_SM, 'label' => __('Show/Hide Shipping Method')],
                    ['value' => Rule::ACTION_USE_ALT_TITLE, 'label' => __('Use Shipping Method Alternative Title')],
                ],
                'data-form-part' => QuoteDataProvider::FORM_NAME

            ]
        );

        $actionType->setAfterElementHtml(
            "<script>
                function changeActions(selectItem){
                    var item = jQuery(selectItem),
                        selectedValue = item.val(),
                        allOptions = item[0].options,
                        notSelectedOptions = [];
                    
                    jQuery.each(allOptions, function(index, item) {
                        if (jQuery.inArray(item.value, selectedValue) == -1) {
                            notSelectedOptions.push(item.value);
                        }
                    });

                    jQuery.each(selectedValue, function(index, item) {
                        var targetClass = '.dependable_fieldset_'+item;
                        var target = jQuery(targetClass);
                          if (target.hasClass('hidden')) {
                              target.toggleClass('hidden');
                          }
                    });
                    
                    //hide not selected fields
                    if (notSelectedOptions.length > 0) {
                         jQuery.each(notSelectedOptions, function(index, item) {
                            var targetClass = '.dependable_fieldset_'+item;
                            var target = jQuery(targetClass);
                            if (!target.hasClass('hidden')) {
                                target.toggleClass('hidden');
                            }
                        });
                    }
                }
            </script>"
        );

        $mainFieldset->addField(
            RuleInterface::ACTION_TYPE_OPTION,
            'select',
            [
                'label' => __('Show/Hide Shipping Method'),
                'title' => __('Show/Hide Shipping Method'),
                'name' => RuleInterface::ACTION_TYPE_OPTION,
                'options' => $this->actionTypeOptions->toArray(),
                'data-form-part' => QuoteDataProvider::FORM_NAME,
            ]
        );

        //Stop future rules processing
        $mainFieldset->addField(
            RuleInterface::STOP_RULES_PROCESSING,
            'select',
            [
                'label' => __('Stop Further Processing'),
                'title' => __('Stop Further Processing'),
                'name' => RuleInterface::STOP_RULES_PROCESSING,
                'options' => $this->yesNoConfig->toArray(),
                'data-form-part' => QuoteDataProvider::FORM_NAME
            ]
        );

        return $mainFieldset;
    }

    /**
     * Add fields
     *
     * @param array $data
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @param Rule $model
     * @param string $parentLabel
     * @return void
     */
    protected function _addAmountFields(
        $data,
        \Magento\Framework\Data\Form\Element\Fieldset $fieldset,
        Rule $model,
        $parentLabel = ''
    ) {
        foreach ($data as $action) {
            if (empty($action['value'])) {
                continue;
            }

            if (is_array($action['value'])) {
                $this->_addAmountFields($action['value'], $fieldset, $model, $action['label']);
            } else {
                $classes = ['validate-not-negative-number', 'hidden-field', 'amount-field', $action['value']];
                $class = implode(' ', $classes);
                $label = $action['label'];
                if ($parentLabel) {
                    $label = $parentLabel . ' [' . $label . ']';
                }
                $fieldset->addField(
                    'amount_' . $action['value'] . '_value',
                    'text',
                    [
                        'name' => 'amount[' . $action['value'] . '][value]',
                        'required' => false,
                        'class' => $class,
                        'label' => $label,
                        'data-form-part' => QuoteDataProvider::FORM_NAME
                    ]
                );
                $fieldset->addField(
                    'amount_' . $action['value'] . '_sort',
                    'text',
                    [
                        'name' => 'amount[' . $action['value'] . '][sort]',
                        'required' => false,
                        'class' => $class,
                        'label' => 'Sort order',
                        'data-form-part' => QuoteDataProvider::FORM_NAME
                    ]
                );
            }
        }
    }

    /**
     * Return source model action type or empty array
     *
     * @return array
     */
    protected function getSourceModelActionType()
    {
        $modelActionType = $this->sourceModel->getActionType() ?: [];

        return $modelActionType;
    }

    /**
     * Return all shipping methods as option array
     *
     * @return array
     */
    protected function getShippingMethods()
    {
        return $this->shippingConfig->toOptionArray();
    }
}
