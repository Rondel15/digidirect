<?php

namespace Digidirect\ProductOverlay\Controller\Adminhtml\Overlays;

/**
 * Class Delete
 * @package Digidirect\ProductOverlay\Controller\Adminhtml\Overlays
 */
class Delete extends \Digidirect\ProductOverlay\Controller\Adminhtml\Overlays
{
    /**
     * Overlay Delete Action
     *
     * @return void
     */
    public function execute()
    {
        /**
         * @var \Magento\Framework\Message\ManagerInterface $messageManager
         */
        $messageManager = $this->getMessageManager();
        $id = (int)$this->getRequest()->getParam('id');
        if ($id) {
            try {
                $this->_overlayRepository->deleteById($id);

                $messageManager->addSuccessMessage(__('You deleted the overlay.'));
                $this->_redirect('digidirect_productoverlay/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $messageManager->addErrorMessage(
                    __('We can\'t delete item right now. Please review the log and try again.')
                );
                $this->_logger->critical($e);
                $this->_redirect('digidirect_productoverlay/*/edit', ['id' => $id]);
                return;
            }
        }
        $messageManager->addErrorMessage(__('We can\'t find a item to delete.'));
        $this->_redirect('digidirect_productoverlay/*/');
    }
}
