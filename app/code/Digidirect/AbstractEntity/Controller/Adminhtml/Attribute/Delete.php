<?php
namespace Digidirect\AbstractEntity\Controller\Adminhtml\Attribute;

use Digidirect\AbstractEntity\Controller\Adminhtml\Attribute as AttributeController;
use Magento\Framework\Exception\LocalizedException;

class Delete extends AttributeController
{
    /**
     * Delete attribute action
     *
     * @return void
     */
    public function execute()
    {
        $attributeId = $this->getRequest()->getParam('attribute_id');
        if ($attributeId) {
            $attributeObject = $this->_initAttribute()->load($attributeId)->setCanManageOptionLabels(true);
            if ($attributeObject->getEntityTypeId() != $this->_getEntityType()->getId() ||
                !$attributeObject->getIsUserDefined()
            ) {
                $this->messageManager->addErrorMessage(__('You cannot delete this attribute.'));
                $this->_redirect('*/*/');
                return;
            }
            try {
                $attributeObject->delete();
                $this->messageManager->addSuccessMessage(__('You deleted the Abstract Entity attribute.'));
                $this->_redirect('*/*/');
                return;
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_redirect('*/*/edit', ['attribute_id' => $attributeId, '_current' => true]);
                return;
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('We can\'t delete the attribute right now.'));
                $this->_redirect('*/*/edit', ['attribute_id' => $attributeId, '_current' => true]);
                return;
            }
        }

        $this->_redirect('*/*/');
        return;
    }
}
