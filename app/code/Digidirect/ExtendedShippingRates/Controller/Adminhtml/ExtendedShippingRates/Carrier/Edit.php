<?php
namespace Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Carrier;

class Edit extends \Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Carrier
{
    /**
     * Carrier edit action
     *
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        /** @var \Digidirect\ExtendedShippingRates\Model\Carrier $model */
        $model = $this->carrierFactory->create();

        if ($id) {
            try {
                $model = $this->carrierRepository->getById($id);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__($e->getMessage()));
                $this->_redirect('digidirect_extendedshippingrates/*');
                return;
            }
        }

        // set entered data if was error when we do save
        $data = $this->_session->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        $this->coreRegistry->register('current_carrier', $model);
        $this->_initAction();
        $breadcrumb = $id ? __('Edit Carrier') : __('New Carrier');
        $this->_addBreadcrumb($breadcrumb, $breadcrumb);

        $title = $model->getCarrierId() ? $model->getName() : __('New Carrier');
        $this->_view->getPage()->getConfig()->getTitle()->prepend($title);
        $this->_view->renderLayout();
    }
}
