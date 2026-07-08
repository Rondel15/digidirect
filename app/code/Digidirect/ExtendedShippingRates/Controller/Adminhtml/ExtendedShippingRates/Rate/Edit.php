<?php
namespace Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Rate;

use \Digidirect\ExtendedShippingRates\Model\Carrier\Method\Rate;

class Edit extends \Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Rate
{
    /**
     * Rate edit action
     *
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if (!$id) {
            $id = $this->getRequest()->getParam('rate_id');
        }
        /** @var \Digidirect\ExtendedShippingRates\Model\Carrier\Method\Rate $model */
        $model = $this->rateFactory->create();

        if ($id) {
            try {
                $model = $this->rateRepository->getById($id);
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

        $this->coreRegistry->register(Rate::CURRENT_RATE, $model);
        $this->_initAction();
        $this->_addBreadcrumb($id ? __('Edit Rate') : __('New Rate'), $id ? __('Edit Rate') : __('New Rate'));

        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $model->getId() ? $model->getTitle() : __('New Rate')
        );
        $this->_view->renderLayout();
    }
}
