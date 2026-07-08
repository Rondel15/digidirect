<?php

namespace Digidirect\ProductOverlay\Block\Adminhtml\Overlays\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Convert\DataObject as ObjectConverter;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Digidirect\ProductOverlay\Model\Overlays;
use Digidirect\ProductOverlay\Model\Source\CatalogPriceRules;
use Digidirect\ProductOverlay\Model\Rule\CatalogRuleOverlays;
use Digidirect\ProductOverlay\Helper\Timezone;

/**
 * Class Conditions
 * @package Digidirect\ProductOverlay\Block\Adminhtml\Overlays\Edit\Tab
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Conditions extends Generic implements TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\Framework\Convert\DataObject
     */
    protected $_objectConverter;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    protected $_rendererFieldset;

    /**
     * @var \Magento\Rule\Block\Conditions
     */
    protected $_conditions;

    /**
     * @var GroupRepositoryInterface
     */
    protected $_groupRepository;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $_yesnoSource;

    /**
     * @var \Digidirect\ProductOverlay\Model\Overlay\Attribute\Source\IsSale
     */
    protected $_isSaleSource;

    /**
     * @var \Digidirect\ProductOverlay\Model\Overlay\Attribute\Source\IsNew
     */
    protected $_isNewSource;

    /**
     * @var \Digidirect\ProductOverlay\Model\Overlay\Attribute\Source\StockStatus
     */
    protected $_stockStatusSource;

    /**
     * @var \Digidirect\ProductOverlay\Model\Overlay\Attribute\Source\ByPrice
     */
    protected $_byPriceSource;

    /**
     * @var \Digidirect\ProductOverlay\Model\RuleFactory
     */
    protected $_overlayRuleFactory;

    /**
     * @var \Digidirect\ProductOverlay\Helper\Data
     */
    protected $_overlayHelper;

    /**
     * @var CatalogPriceRules
     */
    protected $catalogPriceRules;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serializer;

    /**
     * @var CatalogRuleOverlays
     */
    protected $catalogRuleOverlays;

    /**
     * @var Timezone
     */
    protected $timezone;

    /**
     * Conditions constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param ObjectConverter $objectConverter
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset
     * @param \Magento\Rule\Block\Conditions $conditions
     * @param \Magento\Config\Model\Config\Source\Yesno $yesnoSource
     * @param \Digidirect\ProductOverlay\Model\RuleFactory $overlayRuleFactory
     * @param \Digidirect\ProductOverlay\Model\Overlay\Attribute\Source\IsSale $isSaleSource
     * @param \Digidirect\ProductOverlay\Model\Overlay\Attribute\Source\IsNew $isNewSource
     * @param \Digidirect\ProductOverlay\Model\Overlay\Attribute\Source\StockStatus $stockStatusSource
     * @param \Digidirect\ProductOverlay\Model\Overlay\Attribute\Source\ByPrice $byPriceSource
     * @param \Digidirect\ProductOverlay\Helper\Data $overlayHelper
     * @param CatalogPriceRules $catalogPriceRules
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @param array $data
     * @param CatalogRuleOverlays $catalogRuleOverlays
     * @param Timezone $timezone
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        ObjectConverter $objectConverter,
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        \Magento\Rule\Block\Conditions $conditions,
        \Magento\Config\Model\Config\Source\Yesno $yesnoSource,
        \Digidirect\ProductOverlay\Model\RuleFactory $overlayRuleFactory,
        \Digidirect\ProductOverlay\Model\Overlay\Attribute\Source\IsSale $isSaleSource,
        \Digidirect\ProductOverlay\Model\Overlay\Attribute\Source\IsNew $isNewSource,
        \Digidirect\ProductOverlay\Model\Overlay\Attribute\Source\StockStatus $stockStatusSource,
        \Digidirect\ProductOverlay\Model\Overlay\Attribute\Source\ByPrice $byPriceSource,
        \Digidirect\ProductOverlay\Helper\Data $overlayHelper,
        CatalogPriceRules $catalogPriceRules,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        array $data = [],
        CatalogRuleOverlays $catalogRuleOverlays = null,
        Timezone $timezone = null
    ) {
        $this->_systemStore = $systemStore;
        $this->_objectConverter = $objectConverter;
        $this->_groupRepository = $groupRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_conditions = $conditions;
        $this->_rendererFieldset = $rendererFieldset;
        $this->_overlayRuleFactory = $overlayRuleFactory;
        $this->_yesnoSource = $yesnoSource;
        $this->_isSaleSource = $isSaleSource;
        $this->_isNewSource = $isNewSource;
        $this->_byPriceSource = $byPriceSource;
        $this->_stockStatusSource = $stockStatusSource;
        $this->_overlayHelper = $overlayHelper;
        $this->catalogPriceRules = $catalogPriceRules;
        $this->serializer = $serializer;
        $this->catalogRuleOverlays = $catalogRuleOverlays ?: ObjectManager::getInstance()->get(
            CatalogRuleOverlays::class
        );
        $this->timezone = $timezone ?: ObjectManager::getInstance()->get(
            Timezone::class
        );
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Conditions');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Conditions');
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _prepareForm()
    {
        /** @var \Digidirect\ProductOverlay\Model\Overlays $overlay */
        $overlay = $this->_coreRegistry->registry(Overlays::CURRENT_OVERLAY_REGISTRY);
        $overlay->prepareDateRangeValues();

        /** @var \Digidirect\ProductOverlay\Model\Rule $ruleModel */
        $ruleModel = $this->_overlayRuleFactory->create();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        /* start condition block*/
        if ($condSerialize = $overlay->getCondSerialize()) {
            $ruleModel->setConditions([]);
            $ruleModel->setConditionsSerialized($condSerialize);
            $ruleModel->getConditions()->setJsFormObject('rule_conditions_fieldset');
        }

        if ("" != $overlay->getCustomerGroupIds()) {
            $overlay->setCustomerGroupIds($this->serializer->unserialize($overlay->getCustomerGroupIds()));
        }

        $catalogPriceRules = $this->catalogRuleOverlays->getCatalogRulesByOverlayId($overlay->getId(), true);
        if (!empty($catalogPriceRules)) {
            $renderer = $this->_rendererFieldset
                ->setTemplate('Digidirect_ProductOverlay::overlay/catalog_price_rules.phtml')
                ->setOverlay($overlay)
                ->setRules($catalogPriceRules)
                ->setCssClass('message message-warning')
                ->setRemoveLinkImage($this->_assetRepo->getUrl('images/rule_component_remove.gif'));

            $fieldset = $form->addFieldset(
                'conditions_fieldset',
                ['legend' => __('Conditions')]
            );

            $fieldset->setRenderer($renderer);
        } else {
            $renderer = $this->_rendererFieldset->setTemplate(
                'Magento_CatalogRule::promo/fieldset.phtml'
            )->setNewChildUrl(
                $this->getUrl('catalog_rule/promo_catalog/newConditionHtml/form/rule_conditions_fieldset')
            );

            $fieldset = $form->addFieldset(
                'conditions_fieldset',
                ['legend' => __('Conditions')]
            )->setRenderer(
                $renderer
            );

            $fieldset->addField(
                'conditions',
                'text',
                [
                    'name' => 'conditions',
                    'label' => __('Product Conditions'),
                    'title' => __('Product Conditions'),
                    'required' => true
                ]
            )->setRule(
                $ruleModel
            )->setRenderer(
                $this->_conditions
            );
            /* end condition block*/

            /* start date block*/
            $fldDateRange = $form->addFieldset('timeline', ['legend' => __('Date Range')]);

            $dateEnabled = $fldDateRange->addField(
                Overlays::DATE_RANGE_ENABLED,
                'select',
                [
                    'label' => __('Use Date Range'),
                    'title' => __('Use Date Range'),
                    'name' => Overlays::DATE_RANGE_ENABLED,
                    'options' => $this->_yesnoSource->toArray(),
                ]
            );

            $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
            $fromDate = $fldDateRange->addField(
                Overlays::FROM_DATE,
                'date',
                [
                    'name' => Overlays::FROM_DATE,
                    'label' => __('From Date'),
                    'title' => __('From Date'),
                    'input_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
                    'date_format' => $dateFormat
                ]
            );

            $fromTime = $fldDateRange->addField(
                'from_time',
                'text',
                [
                    'name' => 'from_time',
                    'label' => __('From Time'),
                    'title' => __('From Time'),
                    'note' => __('In format 15:32'),
                ]
            );

            $toDate = $fldDateRange->addField(
                Overlays::TO_DATE,
                'date',
                [
                    'name' => Overlays::TO_DATE,
                    'label' => __('To Date'),
                    'title' => __('To Date'),
                    'input_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
                    'date_format' => $dateFormat
                ]
            );

            $toTime = $fldDateRange->addField(
                'to_time',
                'text',
                [
                    'name' => 'to_time',
                    'label' => __('To Time'),
                    'title' => __('To Time'),
                    'note' => __('In format 19:32'),
                ]
            );
            /* end date block*/

            /* start sale block*/
            $fldState = $form->addFieldset('state', ['legend' => __('State')]);

            $fldState->addField(
                Overlays::IS_NEW,
                'select',
                [
                    'label' => __('Is New'),
                    'name' => Overlays::IS_NEW,
                    'values' => $this->_isNewSource->toArray(),
                ]
            );

            $isSale = $fldState->addField(
                Overlays::IS_SALE,
                'select',
                [
                    'label' => __('Is on Sale'),
                    'name' => Overlays::IS_SALE,
                    'values' => $this->_isSaleSource->toArray(),
                ]
            );

            $specialPriceOnly = $fldState->addField(
                Overlays::SPECIAL_PRICE_ONLY,
                'select',
                [
                    'label' => __('Use Special Price Only'),
                    'name' => Overlays::SPECIAL_PRICE_ONLY,
                    'note' => __('For `On Sale` condition'),
                    'values' => $this->_yesnoSource->toArray(),
                ]
            );

            $fldStock = $form->addFieldset('stock', ['legend' => __('Stock')]);
            $stk = $fldStock->addField(
                Overlays::STOCK_STATUS,
                'select',
                [
                    'label' => __('Status'),
                    'name' => Overlays::STOCK_STATUS,
                    'values' => $this->_stockStatusSource->toArray(),
                ]
            );

            $customStockFrom = $fldStock->addField(
                Overlays::STOCK_FROM,
                'text',
                [
                    'name' => Overlays::STOCK_FROM,
                    'label' => __('From Stock'),
                    'title' => __('From Stock'),
                    'class' => 'validate-number'
                ]
            );

            $customStockTo = $fldStock->addField(
                Overlays::STOCK_TO,
                'text',
                [
                    'name' => Overlays::STOCK_TO,
                    'label' => __('To Stock'),
                    'title' => __('To Stock'),
                    'class' => 'validate-number'
                ]
            );

            $fldPriceRange = $form->addFieldset('price', ['legend' => __('Price Range')]);
            $priceEnabled = $fldPriceRange->addField(
                Overlays::PRICE_RANGE_ENABLED,
                'select',
                [
                    'label' => __('Use Price Range'),
                    'title' => __('Use Price Range'),
                    'name' => Overlays::PRICE_RANGE_ENABLED,
                    'options' => $this->_yesnoSource->toArray(),
                ]
            );

            $byPrice = $fldPriceRange->addField(
                Overlays::BY_PRICE,
                'select',
                [
                    'label' => __('By Price'),
                    'title' => __('By Price'),
                    'name' => Overlays::BY_PRICE,
                    'options' => $this->_byPriceSource->toArray(),
                ]
            );

            $fromPrice = $fldPriceRange->addField(
                Overlays::FROM_PRICE,
                'text',
                [
                    'name' => Overlays::FROM_PRICE,
                    'label' => __('From Price'),
                    'title' => __('From Price'),
                ]
            );

            $toPrice = $fldPriceRange->addField(
                Overlays::TO_PRICE,
                'text',
                [
                    'name' => Overlays::TO_PRICE,
                    'label' => __('To Price'),
                    'title' => __('To Price'),
                ]
            );

            $fldGroup = $form->addFieldset('customer_group', ['legend' => __('Customer Groups')]);
            $groupEnabled = $fldGroup->addField(
                Overlays::CUSTOMER_GROUP_ENABLED,
                'select',
                [
                    'label' => __('Use Customer Groups'),
                    'title' => __('Use Customer Groups'),
                    'name' => Overlays::CUSTOMER_GROUP_ENABLED,
                    'options' => $this->_yesnoSource->toArray(),
                ]
            );
            $customerGroups = $this->_groupRepository->getList($this->_searchCriteriaBuilder->create())->getItems();
            $groups = $fldGroup->addField(
                Overlays::CUSTOMER_GROUP_IDS,
                'multiselect',
                [
                    'label' => __('For Customer Groups'),
                    'title' => __('For Customer Groups'),
                    'name' => Overlays::CUSTOMER_GROUP_IDS . '[]',
                    'values' => $this->_objectConverter->toOptionArray($customerGroups, 'id', 'code'),
                    'required' => true
                ]
            );
            /* end sale block*/

            $priceRulesFieldSet = $form->addFieldset('price_rule', ['legend' => __('Private Sales')]);
            $priceRuleEnabledField = $priceRulesFieldSet->addField(
                Overlays::PRIVATE_SALES_ENABLED,
                'select',
                [
                    'label' => __('Use Price Rules'),
                    'title' => __('Use Price Rules'),
                    'name' => Overlays::PRIVATE_SALES_ENABLED,
                    'options' => $this->_yesnoSource->toArray(),
                ]
            );

            $catalogPriceRulesMultiSelect = $priceRulesFieldSet->addField(
                Overlays::CATALOG_PRICE_RULES_IDS,
                'multiselect',
                [
                    'label' => __('For Price Rules'),
                    'title' => __('For Price Rules'),
                    'name' => Overlays::CATALOG_PRICE_RULES_IDS,
                    'values' => $this->catalogPriceRules->toOptionArray(),
                    'required' => true
                ]
            );

            $data = $overlay->getData();
            if ($data) {
                $data['is_active'] = '1';

                if (isset($data[Overlays::FROM_DATE])) {
                    try {
                        $dateFrom = $this->timezone->convertToTz($data[Overlays::FROM_DATE]);
                    } catch (\Exception $e) {
                        $dateFrom = $data[Overlays::FROM_DATE];
                    }
                    $dateFrom = explode(" ", $dateFrom);

                    //This line is required for Magento <= 2.3.0:
                    //$data[Overlays::FROM_DATE] = $dateFrom[0];

                    if (isset($dateFrom[1])) {
                        $data['from_time'] = $this->_overlayHelper->formatTime($dateFrom[1]);
                    }
                }

                if (isset($data[Overlays::TO_DATE])) {
                    try {
                        $dateTo = $this->timezone->convertToTz($data[Overlays::TO_DATE]);
                    } catch (\Exception $e) {
                        $dateTo = $data[Overlays::TO_DATE];
                    }
                    $dateTo = explode(" ", $dateTo);

                    //This line is required for Magento <= 2.3.0:
                    //$data[Overlays::FROM_DATE] = $dateFrom[0];

                    if (isset($dateTo[1])) {
                        $data['to_time'] = $this->_overlayHelper->formatTime($dateTo[1]);
                    }
                }

                //set form values
                $form->setValues($data);
            }

            // define field dependencies
            /**
             * @var \Magento\Backend\Block\Widget\Form\Element\Dependence
             */
            $dependence = $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Form\Element\Dependence::class)
                // Customer Groups
                ->addFieldMap($groupEnabled->getHtmlId(), $groupEnabled->getName())
                ->addFieldMap($groups->getHtmlId(), $groups->getName())
                ->addFieldDependence(
                    $groups->getName(),
                    $groupEnabled->getName(),
                    '1'
                )// Price Range
                ->addFieldMap($priceEnabled->getHtmlId(), $priceEnabled->getName())
                ->addFieldMap($byPrice->getHtmlId(), $byPrice->getName())
                ->addFieldMap($fromPrice->getHtmlId(), $fromPrice->getName())
                ->addFieldMap($toPrice->getHtmlId(), $toPrice->getName())
                ->addFieldDependence(
                    $byPrice->getName(),
                    $priceEnabled->getName(),
                    '1'
                )
                ->addFieldDependence(
                    $fromPrice->getName(),
                    $priceEnabled->getName(),
                    '1'
                )
                ->addFieldDependence(
                    $toPrice->getName(),
                    $priceEnabled->getName(),
                    '1'
                )// Is on Sale
                ->addFieldMap($isSale->getHtmlId(), $isSale->getName())
                ->addFieldMap($specialPriceOnly->getHtmlId(), $specialPriceOnly->getName())
                ->addFieldDependence(
                    $specialPriceOnly->getName(),
                    $isSale->getName(),
                    '2'
                )// Date Range
                ->addFieldMap($dateEnabled->getHtmlId(), $dateEnabled->getName())
                ->addFieldMap($fromDate->getHtmlId(), $fromDate->getName())
                ->addFieldMap($fromTime->getHtmlId(), $fromTime->getName())
                ->addFieldMap($toDate->getHtmlId(), $toDate->getName())
                ->addFieldMap($toTime->getHtmlId(), $toTime->getName())
                ->addFieldDependence(
                    $fromDate->getName(),
                    $dateEnabled->getName(),
                    '1'
                )
                ->addFieldDependence(
                    $fromTime->getName(),
                    $dateEnabled->getName(),
                    '1'
                )
                ->addFieldDependence(
                    $toDate->getName(),
                    $dateEnabled->getName(),
                    '1'
                )
                ->addFieldDependence(
                    $toTime->getName(),
                    $dateEnabled->getName(),
                    '1'
                )->addFieldMap(
                    $priceRuleEnabledField->getHtmlId(),
                    $priceRuleEnabledField->getName()
                )->addFieldMap(
                    $catalogPriceRulesMultiSelect->getHtmlId(),
                    $catalogPriceRulesMultiSelect->getName()
                )->addFieldDependence(
                    $catalogPriceRulesMultiSelect->getName(),
                    $priceRuleEnabledField->getName(),
                    1
                )->addFieldMap(
                    $stk->getHtmlId(),
                    $stk->getName()
                )->addFieldMap(
                    $customStockFrom->getHtmlId(),
                    $customStockFrom->getName()
                )->addFieldMap(
                    $customStockTo->getHtmlId(),
                    $customStockTo->getName()
                )->addFieldDependence(
                    $customStockFrom->getName(),
                    $stk->getName(),
                    3
                )->addFieldDependence(
                    $customStockTo->getName(),
                    $stk->getName(),
                    3
                );

            $this->setChild('form_after', $dependence);
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
