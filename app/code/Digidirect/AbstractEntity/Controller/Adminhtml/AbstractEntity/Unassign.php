<?php
namespace Digidirect\AbstractEntity\Controller\Adminhtml\AbstractEntity;

use Digidirect\AbstractEntity\Controller\Adminhtml\AbstractEntity as AbstractEntityController;
use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Unassign
 * @package Digidirect\AbstractEntity\Controller\Adminhtml\AbstractEntity
 */
class Unassign extends AbstractEntityController
{
    /**
     * Unassign action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $parentId = (int)$this->getRequest()->getParam('parent_id');
        $setId = (int)$this->getRequest()->getParam(AbstractEntityInterface::ATTRIBUTE_SET_ID);
        $id = (int)$this->getRequest()->getParam('id');
        if ($id) {

            try {
                $model = $this->abstractEntityRepository->getById($id);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', [
                    'id' => $parentId,
                    AbstractEntityInterface::ATTRIBUTE_SET_ID => $setId
                ]);
            }

            $model->setParentId(0);
            try {
                $this->abstractEntityRepository->save($model);
                $this->messageManager->addSuccessMessage(__('You unassigned the record.'));
                return $resultRedirect->setPath('*/*/edit', [
                    'id' => $parentId,
                    AbstractEntityInterface::ATTRIBUTE_SET_ID => $setId
                ]);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving.'));
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a record to unassign.'));
        return $resultRedirect->setPath('*/*/edit', [
            'id' => $parentId,
            AbstractEntityInterface::ATTRIBUTE_SET_ID => $setId
        ]);
    }
}
