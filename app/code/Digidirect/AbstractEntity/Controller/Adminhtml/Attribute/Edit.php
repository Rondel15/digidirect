<?php
namespace Digidirect\AbstractEntity\Controller\Adminhtml\Attribute;

use Digidirect\AbstractEntity\Controller\Adminhtml\Attribute as AttributeController;

class Edit extends AttributeController
{
    /**
     * Edit attribute action
     *
     * @return void
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $attributeId = $this->getRequest()->getParam('attribute_id');
        $attributeObject = $this->_initAttribute()->setEntityTypeId($this->_getEntityType()->getId());

        if ($attributeId) {
            $attributeObject->load($attributeId);
            if (!$attributeObject->getId()) {
                $this->messageManager->addErrorMessage(__('This attribute no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
            if ($attributeObject->getEntityTypeId() != $this->_getEntityType()->getId()) {
                $this->messageManager->addErrorMessage(__('You cannot edit this attribute.'));
                $this->_redirect('*/*/');
                return;
            }
        }

        $attributeData = $this->_getSession()->getAttributeData(true);
        if (!empty($attributeData)) {
            $attributeObject->setData(array_merge($attributeObject->getData(), $attributeData));
        }
        $attributeObject->setCanManageOptionLabels(true);
        $this->_coreRegistry->register('entity_attribute', $attributeObject);

        $label = $attributeObject->getId() ? __('Edit Attribute') : __('New Attribute');

        $this->_initAction()->_addBreadcrumb($label, $label);
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Abstract Entity Attributes'));
        $title = $attributeId ? $attributeObject->getFrontendLabel() : __('New Abstract Entity Attribute');
        $this->_view->getPage()->getConfig()->getTitle()->prepend($title);
        $this->_view->renderLayout();
    }
}
