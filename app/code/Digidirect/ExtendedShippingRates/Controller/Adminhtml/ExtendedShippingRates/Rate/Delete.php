<?php
namespace Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Rate;

class Delete extends \Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Rate
{
    /**
     * Delete rate action
     *
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                /** @var \Digidirect\ExtendedShippingRates\Model\Carrier\Method\Rate $model */
                $model = $this->rateRepository->getById($id);
                $name = $model->getTitle();
                $this->rateRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('You deleted the rate %1.', [$name]));
                $this->_redirect(
                    'digidirect_extendedshippingrates/extendedshippingrates_method/edit',
                    ['id' => $model->getData('method_id')]
                );
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('We can\'t delete the rate right now. Please review the log and try again.')
                );
                $this->logger->critical($e);
                $this->_redirect('digidirect_extendedshippingrates/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a rate to delete.'));
        $this->_redirect('digidirect_extendedshippingrates/*/');
    }
}
