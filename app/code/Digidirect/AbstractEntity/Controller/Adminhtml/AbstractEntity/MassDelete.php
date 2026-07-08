<?php
namespace Digidirect\AbstractEntity\Controller\Adminhtml\AbstractEntity;

use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\Store;

class MassDelete extends AbstractMassAction
{
    const ADMIN_RESOURCE_PREFIX = 'Digidirect_AbstractEntity::abstractentity_record_delete_';

    /**
     * Process mass status change
     *
     * @param AbstractDb $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractDb $collection)
    {
        $recordsDeleted = 0;
        foreach ($collection->getAllIds() as $id) {
            $this->abstractEntityRepository->deleteById($id);
            $recordsDeleted++;
        }

        if ($recordsDeleted) {
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) were deleted.', $recordsDeleted));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath(static::INDEX_ACTION, [
            AbstractEntityInterface::ATTRIBUTE_SET_ID => $this->_initAttributeSet()->getAttributeSetId()
        ]);
        return $resultRedirect;
    }
}
