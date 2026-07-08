<?php
namespace Digidirect\MyStoreWidget\Block\Adminhtml\Config\Form\Field;

use Digidirect\AbstractEntity\Model\AbstractEntity;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

class AttributeRenderer extends Select
{
    /**
     * Customer groups cache
     *
     * @var array
     */
    protected $_attributes;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * AttributeRenderer constructor.
     * @param Context $context
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AttributeRepositoryInterface $attributeRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributeRepositoryInterface $attributeRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Retrieve attributes
     * @return array
     */
    protected function _getAttributes()
    {
        if ($this->_attributes === null) {
            $this->_attributes = [];
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $attributes = $this->attributeRepository->getList(AbstractEntity::ENTITY_TYPE, $searchCriteria);
            foreach ($attributes->getItems() as $item) {
                /** \Magento\Eav\Model\Entity\Attribute $item */
                $this->_attributes[$item->getAttributeCode()] = $item->getFrontendLabel();
            }
        }
        return $this->_attributes;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            foreach ($this->_getAttributes() as $attributeCode => $attributeLabel) {
                $this->addOption($attributeCode, addslashes($attributeLabel));
            }
        }
        return parent::_toHtml();
    }
}
