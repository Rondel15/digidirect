<?php
namespace Digidirect\AbstractEntity\Controller\Adminhtml\AbstractEntity;

use Digidirect\AbstractEntity\Controller\Adminhtml\AbstractEntity as AbstractEntityController;
use Digidirect\AbstractEntity\Api\AbstractEntityRepositoryInterface;
use Digidirect\AbstractEntity\Model\AbstractEntity;
use Digidirect\AbstractEntity\Model\Registry\Constants;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Edit extends AbstractEntityController
{
    /**
     * Edit action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $storeId = (int)$this->getRequest()->getParam('store');
        $set = $this->_initAttributeSet();
        try {
            if ($id) {
                $model = $this->abstractEntityRepository->getById($id, $storeId);
            } else {
                $model = $this->_objectManager->create(AbstractEntity::class);
            }
            $this->_coreRegistry->register(Constants::CURRENT_ABSTRACT_ENTITY, $model);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('This record no longer exists.'));
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/');
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $setName = $set->getAttributeSetName();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit %1', $setName) : __('New %1', $setName),
            $id ? __('Edit %1', $setName) : __('New %1', $setName)
        );
        $resultPage->getConfig()->getTitle()->prepend($set->getAttributeSetName());
        $resultPage->getConfig()->getTitle()->prepend(
            $model->getId() ? $model->getName() : __('New %1', $setName)
        );

        if (!$model->getId()) {
            $layout = $resultPage->getLayout();
            if ($switchBlock = $layout->getBlock('store_switcher')) {
                $layout->unsetChild($layout->getParentName('store_switcher'), 'store_switcher');
            }
        }

        return $resultPage;
    }
}
