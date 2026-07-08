<?php
namespace Digidirect\LayeredNavigation\Block\Adminhtml\Product\Attribute\Edit\Tab;

use Digidirect\LayeredNavigation\Api\FilterSettingRepositoryInterface;
use Digidirect\LayeredNavigation\Api\Data\FilterSettingInterface;
use Digidirect\LayeredNavigation\Model\Source\DisplayMode;
use Digidirect\LayeredNavigation\Model\Source\MeasureUnit;
use Digidirect\LayeredNavigation\Model\Source\IndexMode;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;

class LayeredNavigation extends \Magento\Backend\Block\Widget\Form\Generic
    implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var Yesno
     */
    protected $yesNo;

    /**
     * @var DisplayMode
     */
    protected $displayMode;

    /**
     * @var MeasureUnit
     */
    protected $measureUnitSource;

    /**
     * @var IndexMode
     */
    protected $indexMode;

    /**
     * @var FilterSettingInterface
     */
    protected $setting;

    /**
     * @var mixed
     */
    protected $attributeObject;

    /**
     * LayeredNavigation constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Yesno $yesNo
     * @param DisplayMode $displayMode
     * @param MeasureUnit $measureUnitSource
     * @param IndexMode $indexMode
     * @param FilterSettingRepositoryInterface $filterSettingRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Yesno $yesNo,
        DisplayMode $displayMode,
        MeasureUnit $measureUnitSource,
        IndexMode $indexMode,
        FilterSettingRepositoryInterface $filterSettingRepository,
        array $data = []
    ) {
        $this->yesNo = $yesNo;
        $this->displayMode = $displayMode;
        $this->measureUnitSource = $measureUnitSource;
        $this->indexMode = $indexMode;
        $this->attributeObject = $registry->registry('entity_attribute');
        $this->setting = $filterSettingRepository->loadByFilterCode($this->attributeObject->getAttributeCode());
        $this->displayMode->setAttributeType($this->attributeObject->getBackendType());
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $form->setDataObject($this->setting);
        $yesnoSource = $this->yesNo->toOptionArray();

        /** @var  $dependence \Magento\SalesRule\Block\Widget\Form\Element\Dependence */
        $dependence = $this->getLayout()->createBlock(
            'Magento\SalesRule\Block\Widget\Form\Element\Dependence'
        );

        $fieldset = $form->addFieldset(
            'layerednavigation_fieldset_filtering',
            ['legend' => __('Filtering'), 'collapsable' => $this->getRequest()->has('popup')]
        );

        $fieldset->addField(
            FilterSettingInterface::FILTER_CODE,
            'hidden',
            [
                'name' => FilterSettingInterface::FILTER_CODE,
                'value' => $this->setting->getFilterCode(),
            ]
        );

        $displayModeField = $fieldset->addField(
            FilterSettingInterface::DISPLAY_MODE,
            'select',
            [
                'name' => FilterSettingInterface::DISPLAY_MODE,
                'label' => __('Display Mode'),
                'title' => __('Display Mode'),
                'values' => $this->displayMode->toOptionArray(),
            ]
        );

        $dependence->addFieldMap(
            $displayModeField->getHtmlId(),
            $displayModeField->getName()
        );

        $multiselectField = $fieldset->addField(
            FilterSettingInterface::IS_MULTISELECT,
            'select',
            [
                'name' => FilterSettingInterface::IS_MULTISELECT,
                'label' => __('Allow Multiselect'),
                'title' => __('Allow Multiselect'),
                'values' => $yesnoSource,
            ]
        );

        $dependence->addFieldMap(
            $multiselectField->getHtmlId(),
            $multiselectField->getName()
        )->addFieldDependence(
            $multiselectField->getName(),
            $displayModeField->getName(),
            \Digidirect\LayeredNavigation\Model\Source\DisplayMode::MODE_DEFAULT
        );

        $enableShowMore = $fieldset->addField(
            FilterSettingInterface::SHOW_MORE_ENABLED,
            'select',
            [
                'name' => FilterSettingInterface::SHOW_MORE_ENABLED,
                'label' => __('Enable Show More'),
                'title' => __('Enable Show More'),
                'values' => $yesnoSource,
            ]
        );

        $dependence->addFieldMap(
            $enableShowMore->getHtmlId(),
            $enableShowMore->getName()
        )->addFieldDependence(
            $enableShowMore->getName(),
            $displayModeField->getName(),
            \Digidirect\LayeredNavigation\Model\Source\DisplayMode::MODE_DEFAULT
        );

        $showMoreCount = $fieldset->addField(
            FilterSettingInterface::SHOW_MORE_COUNT,
            'text',
            [
                'name' => FilterSettingInterface::SHOW_MORE_COUNT,
                'label' => __('Options per Filter'),
                'title' => __('Options per Filter'),
            ]
        );

        $dependence->addFieldMap(
            $showMoreCount->getHtmlId(),
            $showMoreCount->getName()
        )->addFieldDependence(
            $showMoreCount->getName(),
            $enableShowMore->getName(),
            1
        );

        $dependence->addFieldMap(
            $showMoreCount->getHtmlId(),
            $showMoreCount->getName()
        )->addFieldDependence(
            $showMoreCount->getName(),
            $displayModeField->getName(),
            \Digidirect\LayeredNavigation\Model\Source\DisplayMode::MODE_DEFAULT
        );

        if ($this->attributeObject->getBackendType() != 'decimal') {
            $fieldset->addField(
                FilterSettingInterface::HIDE_ONE_OPTION,
                'select',
                [
                    'name' => FilterSettingInterface::HIDE_ONE_OPTION,
                    'label' => __('Hide filter when only one option available'),
                    'title' => __('Hide filter when only one option available'),
                    'values' => $yesnoSource,
                ]
            );
        } else {
            $useCurrencySymbolField = $fieldset->addField(
                'units_label_use_currency_symbol',
                'select',
                [
                    'name' => 'units_label_use_currency_symbol',
                    'label' => __('Measure Units'),
                    'title' => __('Measure Units'),
                    'values' => $this->measureUnitSource->toOptionArray(),
                ]
            );

            $dependence->addFieldMap(
                $useCurrencySymbolField->getHtmlId(),
                $useCurrencySymbolField->getName()
            );

            $unitsLabelField = $fieldset->addField(
                'units_label',
                'text',
                [
                    'name' => 'units_label',
                    'label' => __('Unit label'),
                    'title' => __('Unit label'),
                ]
            );

            $dependence->addFieldMap(
                $unitsLabelField->getHtmlId(),
                $unitsLabelField->getName()
            );

            $dependence->addFieldDependence(
                $unitsLabelField->getName(),
                $useCurrencySymbolField->getName(),
                MeasureUnit::CUSTOM
            );

            $sliderStepField = $fieldset->addField(
                'slider_step',
                'text',
                [
                    'name' => 'slider_step',
                    'label' => __('Slider Step'),
                    'title' => __('Slider Step'),
                ]
            );

            $dependence->addFieldMap(
                $sliderStepField->getHtmlId(),
                $sliderStepField->getName()
            )->addFieldDependence(
                $sliderStepField->getName(),
                $displayModeField->getName(),
                \Digidirect\LayeredNavigation\Model\Source\DisplayMode::MODE_SLIDER
            );
        }

        if ($this->attributeObject->getFrontendInput() != 'price') {
            $seoFieldSet = $form->addFieldset(
                'layerednavigation_fieldset_seo',
                ['legend' => __('SEO')]
            );

            $seoFieldSet->addField(
                'index_mode',
                'select',
                [
                    'name' => 'index_mode',
                    'label' => __('Allow Google to INDEX the Category Page with the Filter Applied'),
                    'title' => __('Allow Google to INDEX the Category Page with the Filter Applied'),
                    'values' => $this->indexMode->toOptionArray(),
                ]
            );

            $seoFieldSet->addField(
                'follow_mode',
                'select',
                [
                    'name' => 'follow_mode',
                    'label' => __('Allow Google to FOLLOW Links on the Category Page with the Filter Applied'),
                    'title' => __('Allow Google to FOLLOW Links on the Category Page with the Filter Applied'),
                    'values' => $this->indexMode->toOptionArray(),
                ]
            );
        }

        $this->setChild(
            'form_after',
            $dependence
        );

        $this->_eventManager->dispatch(
            'digidirect_attribute_form_tab_build_after',
            ['form' => $form, 'setting' => $this->setting]
        );

        $this->setForm($form);
        $data = $this->setting->getData();
        if (isset($data['slider_step'])) {
            $data['slider_step'] = round($data['slider_step'], 4);
        }

        $form->setValues($data);
        return parent::_prepareForm();
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
    public function getTabLabel()
    {
        return __('Layered Navigation');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Layered Navigation');
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}
