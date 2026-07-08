<?php

namespace Digidirect\AbstractEntity\Block\Adminhtml\Set\Main;

use Digidirect\AbstractEntity\Model\Registry\Constants;
use Digidirect\AbstractEntity\Model\ResourceModel\AdditionalAttributes;
use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\Config\Model\Config\Source\Yesno;

class Formset extends \Magento\Catalog\Block\Adminhtml\Product\Attribute\Set\Main\Formset
{
    const SAVE_ACTION = 'digidirect_abstractentity/*/save';
    const PARENT_ATTRIBUTE_SET_ID = 'parent_attribute_set_id';

    /**
     * @var \Digidirect\AbstractEntity\Model\ResourceModel\AdditionalAttributes
     */
    protected $additionalAttributes;

    /**
     * @var \Digidirect\AbstractEntity\Model\ResourceModel\Relation
     */
    protected $_relation;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var Yesno
     */
    protected $yesNo;

    /**
     * Formset constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Digidirect\AbstractEntity\Model\ResourceModel\Relation $relation
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Eav\Model\Entity\Attribute\SetFactory $setFactory
     * @param AdditionalAttributes $additionalAttributes
     * @param Yesno $yesNo
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Digidirect\AbstractEntity\Model\ResourceModel\Relation $relation,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Eav\Model\Entity\Attribute\SetFactory $setFactory,
        AdditionalAttributes $additionalAttributes,
        Yesno $yesNo,
        array $data = []
    ) {
        $this->_relation = $relation;
        $this->additionalAttributes = $additionalAttributes;
        $this->yesNo = $yesNo;
        parent::__construct($context, $registry, $formFactory, $setFactory, $data);
    }

    /**
     * Prepares attribute set form
     *
     * @return void
     */
    protected function _prepareForm()
    {
        $id = $this->getRequest()->getParam('id');
        $disabled = true;
        $attributeSet = $this->_setFactory->create();
        /** @var AttributeSetInterface $data */
        $data = $attributeSet->load($id);

        $parentAttributeSetId = $this->_relation->getParentAttributeSetId($id);
        if ($parentAttributeSetId) {
            $data->setParentAttributeSetId($parentAttributeSetId);
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $fieldSet = $form->addFieldset('set_name', ['legend' => __('Edit Entity Name')]);
        $fieldSet->addField(
            'attribute_set_name',
            'text',
            [
                'label' => __('Name'),
                'note' => __('For internal use'),
                'name' => 'attribute_set_name',
                'required' => true,
                'class' => 'required-entry validate-no-html-tags',
                'value' => $data->getAttributeSetName()
            ]
        );
        $sets = $this->_setFactory->create()->getResourceCollection()->setEntityTypeFilter(
            $this->_coreRegistry->registry(Constants::ENTITY_TYPE)
        )->load()->toOptionArray();

        if (!$id) {
            $disabled = false;
            $fieldSet->addField('gotoEdit', 'hidden', ['name' => 'gotoEdit', 'value' => '1']);

            $fieldSet->addField(
                'skeleton_set',
                'select',
                [
                    'label' => __('Based On'),
                    'name' => 'skeleton_set',
                    'required' => true,
                    'class' => 'required-entry',
                    'values' => $sets
                ]
            );
        }

        array_unshift($sets, ['value' => '', 'label' => __('-- Select --')]);
        $fieldSet->addField(
            self::PARENT_ATTRIBUTE_SET_ID,
            'select',
            [
                'label' => __('Parent Entity'),
                'name' => self::PARENT_ATTRIBUTE_SET_ID,
                'value' => $data->getParentAttributeSetId(),
                'required' => false,
                'disabled' => $disabled,
                'values' => $sets

            ]
        );

        $fieldSet->addField(
            'is_parent_required',
            'select',
            [
                'label' => __('Is Parent Required'),
                'name' => 'is_parent_required',
                'required' => false,
                'class' => 'validate-no-html-tags',
                'value' => $this->_relation->getIsParentRequired($id),
                'values' => $this->yesNo->toOptionArray()
            ]
        );

        $this->_addAdditionalAttributesFieldset($form);

        $form->setMethod('post');
        $form->setUseContainer(true);
        $form->setId('set-prop-form');
        $form->setAction($this->getUrl(static::SAVE_ACTION));
        $form->setOnsubmit('return false;');
        $this->setForm($form);
    }

    /**
     * @param \Magento\Framework\Data\Form $form
     * @return $this
     */
    protected function _addAdditionalAttributesFieldset(\Magento\Framework\Data\Form $form)
    {
        $fieldSet = $form->addFieldset('edit_additional_attributes', ['legend' => __('Edit Additional Attributes')]);
        $fieldSet->addField(
            AdditionalAttributes::URL_KEY,
            'text',
            [
                'label' => __('Url Key'),
                'name' => AdditionalAttributes::URL_KEY,
                'required' => false,
                'class' => 'validate-no-html-tags',
                'value' => $this->_getAdditionalAttribute(AdditionalAttributes::URL_KEY)
            ]
        );

        $fieldSet->addField(
            AdditionalAttributes::DESCRIPTION,
            'textarea',
            [
                'label' => __('Description'),
                'name' => AdditionalAttributes::DESCRIPTION,
                'required' => false,
                'class' => 'validate-no-html-tags',
                'value' => $this->_getAdditionalAttribute(AdditionalAttributes::DESCRIPTION)
            ]
        );

        $fieldSet->addField(
            AdditionalAttributes::VISIBLE_ON_FRONTEND,
            'select',
            [
                'label' => __('Visible On Frontend'),
                'name' => AdditionalAttributes::VISIBLE_ON_FRONTEND,
                'required' => false,
                'class' => 'validate-no-html-tags',
                'value' => $this->_getAdditionalAttribute(AdditionalAttributes::VISIBLE_ON_FRONTEND),
                'values' => $this->yesNo->toOptionArray()
            ]
        );

        $fieldSet->addField(
            AdditionalAttributes::ENTITIES_PER_LISTING_PAGE,
            'text',
            [
                'label' => __('Entities Per Listing Page on Frontend'),
                'name' => AdditionalAttributes::ENTITIES_PER_LISTING_PAGE,
                'required' => false,
                'class' => 'validate-digits',
                'value' => $this->_getAdditionalAttribute(AdditionalAttributes::ENTITIES_PER_LISTING_PAGE)
            ]
        );

        return $this;
    }

    /**
     * @param string $attributeName
     * @return mixed|null
     */
    protected function _getAdditionalAttribute($attributeName)
    {
        $attributes = $this->_getAdditionalAttributes();
        if (isset($attributes[$attributeName])) {
            return $attributes[$attributeName];
        }

        return null;
    }

    /**
     * @return array
     */
    protected function _getAdditionalAttributes()
    {
        $id = $this->getRequest()->getParam('id');

        if ($id && empty($this->attributes)) {
            $this->attributes = $this->additionalAttributes->getAdditionalAttributesById($id);
        }

        return $this->attributes;
    }
}
