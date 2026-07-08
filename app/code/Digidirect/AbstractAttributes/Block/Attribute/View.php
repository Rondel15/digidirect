<?php
namespace Digidirect\AbstractAttributes\Block\Attribute;

use Digidirect\AbstractAttributes\Api\Data\AbstractAttributeInterface;
use Digidirect\AbstractAttributes\Api\OptionRepositoryInterface;
use Digidirect\AbstractAttributes\Api\Data\OptionInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Data\Collection;

/**
 * Class View
 * @package Digidirect\AbstractAttributes\Block\Attribute
 */
class View extends \Magento\Framework\View\Element\Template
{
    const DEFAULT_TEMPLATE = 'view.phtml';

    const CACHE_TAG = 'eaa_attribute_view';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var OptionRepositoryInterface
     */
    protected $optionRepository;

    /**
     * @var AbstractAttributeInterface
     */
    protected $abstractAttribute;

    /**
     * @var OptionInterface[]
     */
    protected $abstractAttributeOptions;

    /**
     * @var \Digidirect\AbstractAttributes\Helper\Data
     */
    protected $helper;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    protected $sortOrderBuilder;

    /**
     * @var string
     */
    protected $defaultTemplate;

    /**
     * View constructor.
     * @param Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param OptionRepositoryInterface $optionRepository
     * @param \Digidirect\AbstractAttributes\Helper\Data $helper
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $coreRegistry,
        OptionRepositoryInterface $optionRepository,
        \Digidirect\AbstractAttributes\Helper\Data $helper,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        array $data
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->optionRepository = $optionRepository;
        $this->helper = $helper;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->defaultTemplate = isset($data['defaultTemplate']) ? $data['defaultTemplate'] : self::DEFAULT_TEMPLATE;
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractAttributeInterface $abstractAttribute
     * @return $this
     */
    public function setAbstractAttribute(AbstractAttributeInterface $abstractAttribute)
    {
        $this->abstractAttribute = $abstractAttribute;
        $this->abstractAttributeOptions = null;
        return $this;
    }

    /**
     * @return AbstractAttributeInterface|false
     */
    public function getAbstractAttribute()
    {
        if ($this->abstractAttribute === null) {
            $attribute = $this->coreRegistry->registry('current_eaa');
            if ($attribute === null) {
                $attribute = false;
            }
            $this->abstractAttribute = $attribute;
        }
        return $this->abstractAttribute;
    }

    /**
     * @param AbstractAttributeInterface $abstractAttribute
     * @throws LocalizedException
     * @return void
     */
    protected function _addBreadcrumbs(AbstractAttributeInterface $abstractAttribute)
    {
        if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbsBlock->addCrumb('home', [
                'label' => __('Home'),
                'title' => __('Go to Home Page'),
                'link' => $this->_storeManager->getStore()->getBaseUrl()
            ]);
            $breadcrumbsBlock->addCrumb('eaa', [
                'label' => $abstractAttribute->getAttributeLabel(),
                'title' => $abstractAttribute->getAttributeLabel()
            ]);
        }
    }

    /**
     * @return OptionInterface[]
     */
    public function getAttributeOptions()
    {
        if ($this->abstractAttributeOptions === null) {
            $this->abstractAttributeOptions = $this->optionRepository->getList(
                $this->searchCriteriaBuilder
                    ->addFilter(OptionInterface::ATTRIBUTE_ID, $this->getAbstractAttribute()->getAttributeId())
                    ->addFilter(OptionInterface::STORE_ID, $this->_storeManager->getStore()->getId())
                    ->addFilter(OptionInterface::STATUS, OptionInterface::STATUS_ENABLED)
                    ->addSortOrder(
                        $this->sortOrderBuilder
                            ->setField(OptionInterface::SORT_ORDER)
                            ->setAscendingDirection()
                            ->create()
                    )->create()
            )->getItems();
        }
        return $this->abstractAttributeOptions;
    }

    /**
     * @return \Digidirect\AbstractAttributes\Model\ResourceModel\Option\Collection
     */
    protected function prepareCollection()
    {
        $collection = $this->optionRepository->getCollection()
            ->addFieldToFilter(OptionInterface::ATTRIBUTE_ID, $this->getAbstractAttribute()->getAttributeId())
            ->addFieldToFilter(OptionInterface::STATUS, OptionInterface::STATUS_ENABLED)
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->setOrder(OptionInterface::SORT_ORDER, Collection::SORT_ORDER_ASC);

        return $collection;
    }

    /**
     * @return \Digidirect\AbstractAttributes\Model\ResourceModel\Option\Collection
     */
    public function getCollection()
    {
        if (!$this->hasData('collection')) {
            $this->setData('collection', $this->prepareCollection());
        }

        return $this->getData('collection');
    }

    /**
     * Prepare global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $abstractAttribute = $this->getAbstractAttribute();
        if (!$abstractAttribute) {
            return parent::_prepareLayout();
        }

        $this->_addBreadcrumbs($abstractAttribute);
        $this->pageConfig->addBodyClass('eaa-' . $abstractAttribute->getAttributeCode());
        $metaTitle = $abstractAttribute->getMetaTitle();
        if (!$metaTitle) {
            $metaTitle = $abstractAttribute->getAttributeLabel();
        }

        $this->pageConfig->getTitle()->set($metaTitle);
        $this->pageConfig->setDescription($this->helper->prepareMetaDescription($abstractAttribute->getMetaDesc()));

        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle($this->escapeHtml($abstractAttribute->getAttributeLabel()));
        }
        /** @var \Magento\Theme\Block\Html\Pager $toolbar */
        if ($toolbar = $this->getLayout()->getBlock('aa.attribute.view.pager')) {
            $toolbar->setShowPerPage(false);
            $toolbar->setLimit($this->getPagerLimit());
            $toolbar->setCollection($this->getCollection());
            $this->setChild('toolbar', $toolbar);
        }

        return parent::_prepareLayout();
    }

    /**
     * @param string $commonLetter
     * @return array
     */
    public function getAlphabeticalGroups($commonLetter = '#')
    {
        $groups = [];
        $options = $this->getAttributeOptions();
        foreach ($options as $option) {
            $label = trim($option->getLabel() . $option->getId());
            $firstChar = strtoupper(substr($label, 0, 1));
            if (ctype_alpha($firstChar)) {
                $groups[$firstChar][$label] = $option;
            } else {
                $groups[$commonLetter][$label] = $option;
            }
        }

        ksort($groups, SORT_STRING);
        if (isset($groups[$commonLetter])) {
            $commonOptions = $groups[$commonLetter];
            unset($groups[$commonLetter]);
            $groups[$commonLetter] = $commonOptions;
        }

        foreach ($groups as &$group) {
            ksort($group, SORT_STRING);
        }

        return $groups;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $abstractAttribute = $this->getAbstractAttribute();
        if (!$abstractAttribute) {
            return '';
        }

        $template = $abstractAttribute->getPageTemplate();
        if ($template == AbstractAttributeInterface::CUSTOM_TEMPLATE) {
            $template = $abstractAttribute->getCustomTemplate();
            if ($template) {
                $template .= '.phtml';
            }
        }

        if (!$template) {
            $template = $this->defaultTemplate;
        }

        $this->setTemplate('Digidirect_AbstractAttributes::attribute/' . $template);
        return parent::_toHtml();
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getAbstractAttribute()->getId()];
    }
}
