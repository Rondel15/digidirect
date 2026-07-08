<?php
namespace Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Method;

class Delete extends \Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Method
{
    /**
     * Delete method action
     *
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                /** @var \Digidirect\ExtendedShippingRates\Model\Carrier\Method $model */
                $model = $this->methodRepository->getById($id);
                $name = $model->getTitle();
                $this->methodRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('You deleted the method %1.', [$name]));
                if ($this->getRequest()->getParam(static::BACK_TO_PARAM) &&
                    $this->getRequest()->getParam(static::BACK_TO_PARAM == static::BACK_TO_CARRIER_PARAM)
                ) {
                    $this->_redirect(
                        'digidirect_extendedshippingrates/extendedshippingrates_carrier/edit',
                        ['id' => $model->getData('carrier_id')]
                    );
                    return;
                }
                $this->_redirect('digidirect_extendedshippingrates/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('We can\'t delete the method right now. Please review the log and try again.')
                );
                $this->logger->critical($e);
                $this->_redirect('digidirect_extendedshippingrates/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a method to delete.'));
        $this->_redirect('digidirect_extendedshippingrates/*/');
    }
}
