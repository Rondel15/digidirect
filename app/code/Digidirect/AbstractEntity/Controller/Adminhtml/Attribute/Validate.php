<?php
namespace Digidirect\AbstractEntity\Controller\Adminhtml\Attribute;

use Digidirect\AbstractEntity\Controller\Adminhtml\Attribute as AttributeController;
use Magento\Framework\DataObject;

class Validate extends AttributeController
{
    /**
     * Validate attribute action
     *
     * @return void
     */
    public function execute()
    {
        $response = new DataObject();
        $response->setError(false);
        $attributeId = $this->getRequest()->getParam('attribute_id');
        if (!$attributeId) {
            $attributeCode = $this->getRequest()->getParam('attribute_code');
            $attributeObject = $this->_initAttribute()->loadByCode(
                $this->_getEntityType()->getId(),
                $attributeCode
            )->setCanManageOptionLabels(
                true
            );
            if ($attributeObject->getId()) {
                $this->messageManager->addErrorMessage(__('An attribute with the same code already exists.'));
                $this->_view->getLayout()->initMessages();
                $response->setError(true);
                $response->setHtmlMessage($this->_view->getLayout()->getMessagesBlock()->getGroupedHtml());
            }
        }
        $this->getResponse()->representJson($response->toJson());
    }
}
