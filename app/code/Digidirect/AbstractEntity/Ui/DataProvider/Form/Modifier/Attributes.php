<?php
namespace Digidirect\AbstractEntity\Ui\DataProvider\Form\Modifier;

use Digidirect\AbstractEntity\Model\AbstractEntity as AbstractEntityModel;
use Digidirect\AbstractEntity\Model\AbstractEntityRepository;
use Digidirect\AbstractEntity\Model\Registry\Constants;
use Digidirect\AbstractEntity\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Magento\Catalog\Model\Attribute\ScopeOverriddenValue;
use Magento\Eav\Api\Data\AttributeGroupInterface;
use Magento\Eav\Api\AttributeGroupRepositoryInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\Entity\Type;
use Magento\Eav\Model\Config;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\DataProvider\EavValidationRules;
use Magento\Ui\Component\Form\Element\Wysiwyg as WysiwygElement;

class Attributes extends AbstractModifier
{
    /**
     * @var array
     */
    protected $metaProperties = [
        'dataType' => 'frontend_input',
        'visible' => 'is_visible',
        'required' => 'is_required',
        'label' => 'frontend_label',
        'sortOrder' => 'sort_order',
        'notice' => 'note',
        'default' => 'default_value',
        'size' => 'multiline_count',
    ];

    /**
     * Form element mapping
     *
     * @var array
     */
    protected $formElement = [
        'text' => 'input',
        'boolean' => 'checkbox',
    ];

    /**
     * @var EavValidationRules
     */
    protected $eavValidationRules;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var AbstractEntityRepository
     */
    protected $abstractEntityRepository;

    /**
     * @var ScopeOverriddenValue
     */
    protected $scopeOverriddenValue;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var AttributeGroupRepositoryInterface
     */
    protected $attributeGroupRepository;

    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var []
     */
    protected $attributeGroups;

    /**
     * @var []
     */
    protected $attributes;

    /**
     * DataProvider constructor.
     * @param EavValidationRules $eavValidationRules
     * @param Config $eavConfig
     * @param Registry $registry
     * @param RequestInterface $request
     * @param AbstractEntityRepository $abstractEntityRepository
     * @param ScopeOverriddenValue $scopeOverriddenValue
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AttributeGroupRepositoryInterface $attributeGroupRepository
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        EavValidationRules $eavValidationRules,
        Config $eavConfig,
        Registry $registry,
        RequestInterface $request,
        AbstractEntityRepository $abstractEntityRepository,
        ScopeOverriddenValue $scopeOverriddenValue,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributeGroupRepositoryInterface $attributeGroupRepository,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->eavValidationRules = $eavValidationRules;
        $this->eavConfig = $eavConfig;
        $this->abstractEntityRepository = $abstractEntityRepository;
        $this->scopeOverriddenValue = $scopeOverriddenValue;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeGroupRepository = $attributeGroupRepository;
        $this->attributeRepository = $attributeRepository;
        parent::__construct(
            $registry,
            $request
        );
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $meta = array_replace_recursive($meta, $this->prepareFieldsMeta());
        return $meta;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @return array
     */
    protected function prepareFieldsMeta()
    {
        $meta = [];
        $sortOrder = 0;
        foreach ($this->getGroups() as $groupCode => $group) {
            $attributes = !empty($this->getAttributes()[$groupCode]) ? $this->getAttributes()[$groupCode] : [];
            if ($attributes) {
                $meta[$groupCode]['children'] = $this->getAttributesMeta(
                    $this->eavConfig->getEntityType(AbstractEntityModel::ENTITY_TYPE),
                    $attributes
                );
                $meta[$groupCode]['arguments']['data']['config']['componentType'] = Fieldset::NAME;
                $meta[$groupCode]['arguments']['data']['config']['label'] = __('%1', $group->getAttributeGroupName());
                $meta[$groupCode]['arguments']['data']['config']['collapsible'] = true;
                $meta[$groupCode]['arguments']['data']['config']['opened'] = true;
                $meta[$groupCode]['arguments']['data']['config']['sortOrder'] = $sortOrder;
            }
            $sortOrder++;
        }
        return $meta;
    }

    /**
     * @param Type $entityType
     * @param array $groupAttributes
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getAttributesMeta(Type $entityType, $groupAttributes = [])
    {
        $meta = [];
        $attributes = $entityType->getAttributeCollection();
        /* @var EavAttribute $attribute */
        foreach ($attributes as $attribute) {
            $code = $attribute->getAttributeCode();
            if (empty($groupAttributes) || array_key_exists($code, $groupAttributes)) {
                foreach ($this->metaProperties as $metaName => $origName) {
                    $value = $attribute->getDataUsingMethod($origName);
                    $meta[$code][$metaName] = $value;
                    if ('frontend_input' === $origName) {
                        $meta[$code]['formElement'] = isset($this->formElement[$value])
                            ? $this->formElement[$value]
                            : $value;
                    }
                    if ($attribute->usesSource()) {
                        $meta[$code]['options'] = $attribute->getSource()->getAllOptions();
                    }
                }

                $rules = $this->eavValidationRules->build($attribute, $meta[$code]);
                if (!empty($rules)) {
                    $meta[$code]['validation'] = $rules;
                }

                $meta[$code]['visible'] = true;
                $meta[$code]['sortOrder'] = $attribute->getPosition();
                $meta[$code]['scopeLabel'] = $this->getScopeLabel($attribute);
                $meta[$code]['componentType'] = Field::NAME;
                $meta[$code]['dataScope'] = $code;
                $meta[$code]['notice'] = $attribute->getNote();
                $meta[$code]['code'] = $attribute->getAttributeCode();
                if ($attribute->getFrontendInput() === 'textarea') {
                    $meta[$code] = $this->customizeWysiwyg($attribute, $meta[$code]);
                }
                if ($attribute->getFrontendInput() === 'boolean') {
                    $meta[$code] = $this->customizeCheckbox($attribute, $meta[$code]);
                }
                if (!$attribute->isScopeGlobal() && $this->getStoreId()) {
                    $meta[$code]['disabled'] = $this->_isDisabled($attribute);
                    $meta[$code]['service'] = [
                        'template' => 'ui/form/element/helper/service',
                    ];
                }
            }
        }

        $result = [];
        foreach ($meta as $field => $fieldMeta) {
            $result[$field]['arguments']['data']['config'] = $fieldMeta;
        }

        return $result;
    }

    /**
     * Add wysiwyg properties
     *
     * @param \Digidirect\AbstractEntity\Model\ResourceModel\Eav\Attribute $attribute
     * @param array $meta
     * @return array
     */
    private function customizeWysiwyg($attribute, array $meta)
    {
        if (!$attribute->getIsWysiwygEnabled()) {
            return $meta;
        }

        $meta['formElement'] = WysiwygElement::NAME;
        $meta['wysiwyg'] = true;
        $meta['template'] = 'ui/form/field';
        $meta['wysiwygConfigData'] = [
            'add_variables' => true,
            'add_widgets' => true,
            'add_directives' => true,
            'use_container' => true,
            'container_class' => 'hor-scroll',
        ];

        return $meta;
    }

    /**
     * Customize checkboxes
     *
     * @param \Digidirect\AbstractEntity\Model\ResourceModel\Eav\Attribute $attribute
     * @param array $meta
     * @return array
     */
    protected function customizeCheckbox($attribute, array $meta)
    {
        $meta['prefer'] = 'toggle';
        $meta['valueMap'] = [
            'true' => '1',
            'false' => '0',
        ];

        return $meta;
    }

    /**
     * Check if field is disabled
     *
     * @param EavAttribute $attribute
     * @return bool
     */
    protected function _isDisabled(EavAttribute $attribute)
    {
        if (!$this->getStoreId()) {
            return false;
        }

        $currentEntity = $this->getCurrentEntity();
        if (!$id = $currentEntity->getId()) {
            return false;
        }

        return !$this->scopeOverriddenValue->containsValue(
            \Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface::class,
            $currentEntity,
            $attribute->getAttributeCode(),
            $this->getStoreId()
        );
    }

    /**
     * @param EavAttribute $attribute
     * @return string
     */
    protected function getScopeLabel(EavAttribute $attribute)
    {
        $html = '';
        if ($attribute->isScopeGlobal()) {
            $html .= __('[GLOBAL]');
        } elseif ($attribute->isScopeWebsite()) {
            $html .= __('[WEBSITE]');
        } elseif ($attribute->isScopeStore()) {
            $html .= __('[STORE VIEW]');
        }

        return $html;
    }

    /**
     * Retrieve groups
     *
     * @return AttributeGroupInterface[]
     */
    protected function getGroups()
    {
        if (!$this->attributeGroups) {
            $searchCriteria = $this->prepareGroupSearchCriteria()->create();
            $attributeGroupSearchResult = $this->attributeGroupRepository->getList($searchCriteria);
            foreach ($attributeGroupSearchResult->getItems() as $group) {
                $this->attributeGroups[$group->getAttributeGroupCode()] = $group;
            }
        }
        return $this->attributeGroups;
    }

    /**
     * Initialize attribute group search criteria with filters.
     *
     * @return SearchCriteriaBuilder
     */
    protected function prepareGroupSearchCriteria()
    {
        return $this->searchCriteriaBuilder->addFilter(
            AttributeGroupInterface::ATTRIBUTE_SET_ID,
            $this->getAttributeSetId()
        );
    }

    /**
     * Return current attribute set id
     *
     * @return int|null
     */
    protected function getAttributeSetId()
    {
        $setId = $this->getCurrentEntity()->getAttributeSetId();
        if (!$setId) {
            return $this->registry->registry(Constants::CURRENT_ATTRIBUTE_SET)->getId();
        }
        return $setId;
    }

    /**
     * Retrieve attributes
     *
     * @return EavAttribute[]
     */
    protected function getAttributes()
    {
        if (!$this->attributes) {
            foreach ($this->getGroups() as $group) {
                $attributes = $this->loadAttributes($group);
                foreach ($attributes as $attribute) {
                    $this->attributes[$group->getAttributeGroupCode()][$attribute->getAttributeCode()] = $attribute;
                }
            }
        }
        return $this->attributes;
    }

    /**
     * Loading product attributes from group
     *
     * @param AttributeGroupInterface $group
     * @return EavAttribute[]
     */
    protected function loadAttributes(AttributeGroupInterface $group)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(AttributeGroupInterface::GROUP_ID, $group->getAttributeGroupId())
            ->create();
        return $this->attributeRepository->getList(AbstractEntityModel::ENTITY_TYPE, $searchCriteria)->getItems();
    }
}
