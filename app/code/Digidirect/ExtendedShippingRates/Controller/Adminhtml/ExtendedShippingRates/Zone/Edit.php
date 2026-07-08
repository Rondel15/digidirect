<?php
namespace Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Zone;

use \Digidirect\ExtendedShippingRates\Model\Zone as ZoneModel;

class Edit extends \Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Zone
{
    /**
     * Shipping zone edit action
     *
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        /** @var ZoneModel $model */
        $model = $this->zoneFactory->create();

        if ($id) {
            try {
                $model = $this->zoneRepository->getById($id);
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

        $model->getConditions()->setJsFormObject('zone_conditions_fieldset');
        $model->getActions()->setJsFormObject('zone_actions_fieldset');

        $this->coreRegistry->register(ZoneModel::CURRENT_ZONE, $model);

        $this->_initAction();
        $this->_addBreadcrumb($id ? __('Edit Zone') : __('New Zone'), $id ? __('Edit Zone') : __('New Zone'));

        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $model->getId() ? $model->getName() : __('New Shipping Zone')
        );
        $this->_view->renderLayout();
    }
}
