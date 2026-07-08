<?php
namespace Digidirect\AbstractEntity\Controller\Adminhtml\AbstractEntity;

use Digidirect\AbstractEntity\Controller\Adminhtml\AbstractEntity as AbstractEntityController;
use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;

class Delete extends AbstractEntityController
{
    const ADMIN_RESOURCE_PREFIX = 'Digidirect_AbstractEntity::abstractentity_record_delete_';

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');
        $setId = $this->getRequest()->getParam(AbstractEntityInterface::ATTRIBUTE_SET_ID);
        if ($id) {
            try {
                $this->abstractEntityRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('You deleted the record.'));
                return $resultRedirect->setPath('*/*/', [AbstractEntityInterface::ATTRIBUTE_SET_ID => $setId]);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', [
                    'id' => $id,
                    AbstractEntityInterface::ATTRIBUTE_SET_ID => $setId,
                ]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a record to delete.'));
        return $resultRedirect->setPath('*/*/', [AbstractEntityInterface::ATTRIBUTE_SET_ID => $setId]);
    }
}
