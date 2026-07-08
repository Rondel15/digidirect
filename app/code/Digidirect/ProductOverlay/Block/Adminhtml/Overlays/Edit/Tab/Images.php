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

/**
 * Class Images
 * @package Digidirect\ProductOverlay\Block\Adminhtml\Overlays\Edit\Tab
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Images extends Generic implements TabInterface
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
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * @var \Digidirect\ProductOverlay\Helper\Data
     */
    protected $_helper;

    /**
     * Images constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param RuleFactory $salesRule
     * @param ObjectConverter $objectConverter
     * @param Store $systemStore
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Digidirect\ProductOverlay\Helper\Data $helper
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
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
        \Digidirect\ProductOverlay\Helper\Data $helper,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        array $data = []
    ) {
        $this->_systemStore           = $systemStore;
        $this->_objectConverter       = $objectConverter;
        $this->_salesRule             = $salesRule;
        $this->groupRepository        = $groupRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_helper                = $helper;
        $this->_wysiwygConfig         = $wysiwygConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Images');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Images');
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

        $fldProduct = $form->addFieldset('product_page', ['legend' => __('Product Page')]);
        // {ATTR:code} - attribute value, {STOCK_QTY} - quantity in stock
        $note = 'Variables: {ATTR:code} - attribute value, {SAVE_PERCENT} - save percents, {SAVE_AMOUNT} - save '
            . 'amount, {PRICE} - price, {SPECIAL_PRICE} special price, {BR} - new line, {NEW_FOR} - how may days '
            . 'ago the product was added, {SKU} - product SKU';

        $fldProduct->addField(
            Overlays::PROD_TXT,
            'editor',
            [
                'name'     => Overlays::PROD_TXT,
                'label'    => __('Image Label'),
                'title'    => __('Image Label'),
                'style'    => 'height:10em',
                'required' => false,
                'config'   => $this->_wysiwygConfig->getConfig(),
                'note'     => __($note),
            ]
        );

        $fldProduct->addField(
            Overlays::PROD_IMG,
            'file',
            [
                'label'              => __('Image'),
                'name'               => Overlays::PROD_IMG,
                'after_element_html' => $this->getImageHtml(Overlays::PROD_IMG, $overlay->getProdImg()),
            ]
        );

        $fldProduct->addField(
            Overlays::PROD_POS,
            'select',
            [
                'label'  => __('Position'),
                'name'   => Overlays::PROD_POS,
                'values' => $overlay->getAvailablePositions(),
            ]
        );

        $fldProduct->addField(
            Overlays::PROD_IMAGE_SIZE,
            'text',
            [
                'label' => __('Image Size'),
                'name'  => Overlays::PROD_IMAGE_SIZE,
                'note'  => __('Percent of the product image.'),
            ]
        );

        $fldProduct->addField(
            Overlays::PROD_STOCK_LABEL,
            'editor',
            [
                'name'     => Overlays::PROD_STOCK_LABEL,
                'label'    => __('Stock Label'),
                'title'    => __('Stock Label'),
                'style'    => 'height:10em',
                'required' => false,
                'config'   => $this->_wysiwygConfig->getConfig(),
                'note'     => __('Variable: {STOCK}'),
            ]
        );

        $fldCat = $form->addFieldset('category_page', ['legend' => __('Category Page')]);
        $fldCat->addField(
            Overlays::CAT_TXT,
            'editor',
            [
                'name'     => Overlays::CAT_TXT,
                'label'    => __('Image Label'),
                'title'    => __('Image Label'),
                'style'    => 'height:10em',
                'required' => false,
                'config'   => $this->_wysiwygConfig->getConfig(),
                'note'     => __($note),
            ]
        );
        $fldCat->addField(
            Overlays::CAT_IMG,
            'file',
            [
                'label'              => __('Image'),
                'name'               => Overlays::CAT_IMG,
                'after_element_html' => $this->getImageHtml(Overlays::CAT_IMG, $overlay->getCatImg()),
            ]
        );
        $fldCat->addField(
            Overlays::CAT_POS,
            'select',
            [
                'label'  => __('Position'),
                'name'   => Overlays::CAT_POS,
                'values' => $overlay->getAvailablePositions(),
            ]
        );

        $fldCat->addField(
            Overlays::CAT_IMAGE_SIZE,
            'text',
            [
                'label' => __('Image Size'),
                'name'  => Overlays::CAT_IMAGE_SIZE,
                'note'  => __('Percent of the product image.'),
            ]
        );

        $form->setValues($overlay->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * @param string $field
     * @param string $img
     * @return string
     */
    protected function getImageHtml($field, $img)
    {
        $html = '';
        if ($img) {
            $html .= '<p style="margin-top: 5px">';
            $html .= '<img style="max-width:300px" src="' . $this->_helper->getImageUrl($img) . '" />';
            $html .= '<br/><input type="checkbox" value="1" name="remove_' . $field . '"/> ' . __('Remove');
            $html .= '<input type="hidden" value="' . $img . '" name="old_' . $field . '"/>';
            $html .= '</p>';
        }
        return $html;
    }
}
