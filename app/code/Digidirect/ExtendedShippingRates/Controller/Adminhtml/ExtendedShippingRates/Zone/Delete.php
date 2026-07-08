<?php
namespace Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Zone;

class Delete extends \Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Zone
{
    /**
     * Delete shipping zone action
     *
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $this->zoneRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('You deleted the zone.'));
                $this->_redirect('digidirect_extendedshippingrates/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('We can\'t delete the zone right now. Please review the log and try again.')
                );
                $this->logger->critical($e);
                $this->_redirect('digidirect_extendedshippingrates/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a zone to delete.'));
        $this->_redirect('digidirect_extendedshippingrates/*/');
    }
}
