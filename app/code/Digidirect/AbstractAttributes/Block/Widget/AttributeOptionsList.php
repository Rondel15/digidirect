<?php
namespace Digidirect\AbstractAttributes\Block\Widget;

use Digidirect\AbstractAttributes\Api\AbstractAttributeRepositoryInterface;
use Digidirect\AbstractAttributes\Api\Data\OptionInterface;
use Digidirect\AbstractAttributes\Api\OptionRepositoryInterface;
use Digidirect\AbstractAttributes\Helper\Image as ImageHelper;
use Digidirect\AbstractAttributes\Model\Widget\Options\Template as TemplateModel;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Data\Collection;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class AttributeOptionsList extends \Magento\Framework\View\Element\Template
    implements \Magento\Widget\Block\BlockInterface
{
    const DEFAULT_IMAGE_WIDTH = 75;

    /**
     * @var OptionRepositoryInterface
     */
    protected $repository;

    /**
     * @var AbstractAttributeRepositoryInterface
     */
    protected $abstractAttributeRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $search;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var SortOrderBuilder
     */
    protected $sortOrderBuilder;

    /**
     * Template data
     * @var [] | null
     */
    protected $templateData;

    /**
     * @var TemplateModel
     */
    protected $templateModel;

    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * AttributeOptionsList constructor.
     * @param Context $context
     * @param OptionRepositoryInterface $repository
     * @param AbstractAttributeRepositoryInterface $abstractAttributeRepository
     * @param SearchCriteriaBuilder $search
     * @param FilterBuilder $filterBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param TemplateModel $templateModel
     * @param ImageHelper $imageHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        OptionRepositoryInterface $repository,
        AbstractAttributeRepositoryInterface $abstractAttributeRepository,
        SearchCriteriaBuilder $search,
        FilterBuilder $filterBuilder,
        SortOrderBuilder $sortOrderBuilder,
        TemplateModel $templateModel,
        ImageHelper $imageHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->repository = $repository;
        $this->abstractAttributeRepository = $abstractAttributeRepository;
        $this->search = $search;
        $this->filterBuilder = $filterBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->templateModel = $templateModel;
        $this->imageHelper = $imageHelper;
    }

    /**
     * Get collection of attribute options
     * @return OptionInterface[]
     */
    public function getOptions()
    {
        $positionSort = $this->sortOrderBuilder
            ->setField(OptionInterface::SORT_ORDER)
            ->setDirection(Collection::SORT_ORDER_ASC)
            ->create();

        /*$idSort = $this->sortOrderBuilder
            ->setField(OptionInterface::ID)
            ->setDirection(Collection::SORT_ORDER_ASC)
            ->create();*/

        $searchCriteria = $this->search
            ->addFilter(OptionInterface::ATTRIBUTE_ID, $this->getData('attribute_id'))
            ->addFilter(OptionInterface::STATUS, OptionInterface::STATUS_ENABLED)
            ->addFilter(OptionInterface::INCLUDE_IN_WIDGET, 1)
            ->addFilter(OptionInterface::STORE_ID, $this->_storeManager->getStore()->getId())
            ->addSortOrder($positionSort)
            //->addSortOrder($idSort)
            ->create();

        if ($optionsCount = (int)$this->getData('option_count')) {
            $searchCriteria->setPageSize($optionsCount);
        }

        $result = $this->repository->getList($searchCriteria);
        return $result->getItems();
    }

    /**
     * Get image from option data
     * @param OptionInterface $option
     * @return DataObject|false
     */
    public function getImage(OptionInterface $option)
    {
        if (!$option->getData(OptionInterface::WIDGET_LOGO)) {
            return false;
        }

        $imageHelper = $this->getImageHelper($option);
        $image['width'] = $this->getTemplateData('image_width') ?: self::DEFAULT_IMAGE_WIDTH;
        $image['height'] = $this->getTemplateData('image_height');
        $image['src'] = $imageHelper->resize($image['width'], $image['height'])->getUrl();
        $image['orig_src'] = $imageHelper->getOriginalImageUrl();
        $image['helper'] = $imageHelper;
        return new DataObject($image);
    }

    /**
     * @param OptionInterface $option
     * @return \Digidirect\AbstractAttributes\Helper\Image
     */
    public function getImageHelper(OptionInterface $option)
    {
        return $this->imageHelper->init($option, OptionInterface::WIDGET_LOGO);
    }

    /**
     * Get attribute
     * @param int|null $attrId
     * @return \Digidirect\AbstractAttributes\Api\Data\AbstractAttributeInterface|null
     */
    public function getAttribute($attrId = null)
    {
        $attrId = $attrId ?: $this->getData('attribute_id');
        if (!$attrId) {
            return null;
        }

        try {
            $abstractAttribute = $this->abstractAttributeRepository->getByAttributeId(
                $attrId,
                $this->_storeManager->getStore()->getId()
            );
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }

        return $abstractAttribute;
    }

    /**
     * Get template data by attribute
     * @param string $attr
     * @return array|null
     */
    public function getTemplateData($attr)
    {
        if (!$this->templateData) {
            $this->templateData = $this->templateModel->getTemplateData(
                $this->getData('widget_template')
            );
        }

        if (!$attr) {
            return $this->templateData;
        }

        if ($this->templateData && isset($this->templateData[$attr])) {
            return $this->templateData[$attr];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if (!($attrId = $this->getData('attribute_id'))
            || !($attr = $this->getAttribute($attrId))
            || !$attr->getStatus()
        ) {
            return '';
        }

        $templatePath = $this->getTemplateData('path');
        if ($this->getData('widget_template') == 'custom') {
            $templatePath = $this->getData('custom_template') . '.phtml';
        }

        $this->setTemplate('Digidirect_AbstractAttributes::widget/attribute_options/' . $templatePath);

        return parent::_toHtml();
    }
}
