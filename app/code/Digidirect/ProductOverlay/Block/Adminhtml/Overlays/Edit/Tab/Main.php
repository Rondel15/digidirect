<?php

namespace Digidirect\ProductOverlay\Block\Adminhtml\Overlays\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Convert\DataObject as ObjectConverter;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\SalesRule\Model\RuleFactory;
use Magento\Store\Model\System\Store;
use Digidirect\ProductOverlay\Model\Overlays;
use Magento\Config\Model\Config\Source\Yesno as SourceYesNo;

/**
 * Class Main
 * @package Digidirect\ProductOverlay\Block\Adminhtml\Overlays\Edit\Tab
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Main extends Generic implements TabInterface
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
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    protected $_salesRule;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * @var \Digidirect\ProductOverlay\Model\Overlay\Attribute\Source\Status
     */
    protected $_statusFactory;

    /**
     * @var SourceYesNo
     */
    protected $_yesnoSource;

    /**
     * Main constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param RuleFactory $salesRule
     * @param ObjectConverter $objectConverter
     * @param Store $systemStore
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Digidirect\ProductOverlay\Model\Overlay\Attribute\Source\Status $statusFactory
     * @param SourceYesNo $yesnoSource
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        RuleFactory $salesRule,
        ObjectConverter $objectConverter,
        Store $systemStore,
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Digidirect\ProductOverlay\Model\Overlay\Attribute\Source\Status $statusFactory,
        SourceYesNo $yesnoSource,
        array $data = []
    ) {
        $this->_systemStore           = $systemStore;
        $this->_objectConverter       = $objectConverter;
        $this->_salesRule             = $salesRule;
        $this->groupRepository        = $groupRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_statusFactory         = $statusFactory;
        $this->_yesnoSource           = $yesnoSource;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('General');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('General');
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
        /** @var \Digidirect\ProductOverlay\Model\Overlays $overlay */
        $overlay = $this->_coreRegistry->registry(Overlays::CURRENT_OVERLAY_REGISTRY);
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('overlay_');

        $fieldset = $form->addFieldset('general', ['legend' => __('Overlay Information')]);

        if ($overlay->getId()) {
            $fieldset->addField(Overlays::OVERLAY_ID, 'hidden', ['name' => Overlays::OVERLAY_ID]);
        }

        $fieldset->addField(
            Overlays::STATUS,
            'select',
            [
                'label'  => __('Overlay status'),
                'name'   => Overlays::STATUS,
                'values' => \Digidirect\ProductOverlay\Model\Overlay\Attribute\Source\Status::getOptionArray(),
            ]
        );

        $fieldset->addField(
            Overlays::NAME,
            'text',
            [
                'name'     => Overlays::NAME,
                'label'    => __('Name'),
                'title'    => __('Name'),
                'required' => true
            ]
        );

        $validateClass = sprintf(
            'validate-not-negative-number validate-length maximum-length-%d',
            5
        );
        $fieldset->addField(
            Overlays::POS,
            'text',
            [
                'label' => __('Priority'),
                'name'  => Overlays::POS,
                'note'  => __('Use 0 to show overlay first, and 99 to show it last'),
                'class' => $validateClass
            ]
        );

        $fieldset->addField(
            Overlays::IS_SINGLE,
            'select',
            [
                'label'  => __('Hide if overlay with higher priority is already applied'),
                'name'   => Overlays::IS_SINGLE,
                'values' => $this->_yesnoSource->toArray(),
            ]
        );

        $fieldset->addField(
            Overlays::USE_FOR_PARENT,
            'select',
            [
                'label'  => __('Use for Parent'),
                'title'  => __('Use for Parent'),
                'name'   => Overlays::USE_FOR_PARENT,
                'note'   => __('Display child`s overlay for parent (configurable and grouped products only)'),
                'values' => $this->_yesnoSource->toArray(),
            ]
        );

        if (!$this->_storeManager->isSingleStoreMode()) {
            $field    = $fieldset->addField(
                Overlays::STORES,
                'multiselect',
                [
                    'label'    => __('Store'),
                    'title'    => __('Store'),
                    'values'   => $this->_systemStore->getStoreValuesForForm(false, true),
                    'name'     => Overlays::STORES,
                    'required' => true
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $field->setRenderer($renderer);
        } else {
            $fieldset->addField(
                Overlays::STORES,
                'hidden',
                ['name' => Overlays::STORES, 'value' => $this->_storeManager->getStore(true)->getId()]
            );
        }

        $form->setValues($overlay->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
