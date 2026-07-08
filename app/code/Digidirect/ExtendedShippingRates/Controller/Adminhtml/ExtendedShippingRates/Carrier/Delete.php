<?php
namespace Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Carrier;

class Delete extends \Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Carrier
{
    /**
     * Delete carrier action
     *
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                /** @var \Digidirect\ExtendedShippingRates\Model\Carrier $model */
                $model = $this->carrierRepository->getById($id);
                $name = $model->getName();
                $this->carrierRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('You deleted the carrier %1.', [$name]));
                $this->_redirect('digidirect_extendedshippingrates/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('We can\'t delete the carrier right now. Please review the log and try again.')
                );
                $this->logger->critical($e);
                $this->_redirect('digidirect_extendedshippingrates/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a carrier to delete.'));
        $this->_redirect('digidirect_extendedshippingrates/*/');
    }
}
